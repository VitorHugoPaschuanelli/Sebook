<?php

declare(strict_types=1);

namespace Sebook;

use DomainException;
use PDO;

final class Inventory
{
    public const CONDITIONS = ['Novo', 'Ótimo', 'Bom', 'Regular'];
    public const REASONS = [
        'entrada' => ['Compra', 'Doação recebida', 'Devolução', 'Correção'],
        'saida' => ['Venda', 'Doação realizada', 'Perda ou avaria', 'Correção'],
    ];

    public function __construct(private PDO $db) {}

    private function text(array $data, string $field, string $label, int $max, bool $required = false): string
    {
        if (isset($data[$field]) && !is_scalar($data[$field])) {
            throw new DomainException("$label inválido.");
        }
        $value = trim((string) ($data[$field] ?? ''));
        if (($required && $value === '') || mb_strlen($value) > $max) {
            throw new DomainException("$label: " . ($required ? 'preencha com até ' : 'use até ') . "$max caracteres.");
        }
        return $value;
    }

    public static function integer(mixed $value, string $label, int $min = 0): int
    {
        if (!is_scalar($value) || !preg_match('/^\d{1,7}$/D', (string) $value) || (int) $value < $min || (int) $value > 1000000) {
            throw new DomainException("$label deve ser um número inteiro entre $min e 1000000.");
        }
        return (int) $value;
    }

    private function bookData(array $input): array
    {
        $data = [];
        foreach (['title' => ['Título', 180, true], 'author' => ['Autor', 150, true], 'category' => ['Categoria', 80, false], 'publisher' => ['Editora', 100, false], 'isbn' => ['ISBN', 20, false], 'location' => ['Localização', 80, false], 'notes' => ['Observações', 2000, false]] as $field => [$label, $max, $required]) {
            $data[$field] = $this->text($input, $field, $label, $max, $required);
        }
        $data['condition'] = $this->text($input, 'condition', 'Estado de conservação', 20, true);
        if (!in_array($data['condition'], self::CONDITIONS, true)) {
            throw new DomainException('Selecione um estado de conservação válido.');
        }
        $price = $this->text($input, 'price', 'Preço', 12, true);
        if (!preg_match('/^\d{1,7}(?:[.,]\d{1,2})?$/D', $price)) {
            throw new DomainException('Informe o preço sem separador de milhar, por exemplo 25,90.');
        }
        $parts = preg_split('/[.,]/', $price);
        $data['price_cents'] = (int) $parts[0] * 100 + (int) str_pad($parts[1] ?? '', 2, '0');
        $data['minimum_stock'] = self::integer($input['minimum_stock'] ?? '', 'Estoque mínimo');
        return $data;
    }

    public function create(array $input): int
    {
        $data = $this->bookData($input);
        $quantity = self::integer($input['quantity'] ?? '', 'Quantidade inicial');
        $this->db->beginTransaction();
        try {
            $data['created_at'] = $data['updated_at'] = date('Y-m-d H:i:s');
            $fields = array_keys($data);
            $sql = 'INSERT INTO books (' . implode(',', array_map(static fn($field) => '"' . $field . '"', $fields)) . ') VALUES (:' . implode(',:', $fields) . ')';
            $this->db->prepare($sql)->execute($data);
            $id = (int) $this->db->lastInsertId();
            if ($quantity > 0) {
                $this->applyMovement($id, 'entrada', $quantity, 'Cadastro inicial', '');
            }
            $this->db->commit();
            return $id;
        } catch (\Throwable $error) {
            $this->db->rollBack();
            throw $error;
        }
    }

    public function update(int $id, array $input): void
    {
        $data = $this->bookData($input);
        $data['id'] = $id;
        $data['updated_at'] = date('Y-m-d H:i:s');
        $sets = [];
        foreach (array_keys($data) as $field) {
            if ($field !== 'id') {
                $sets[] = '"' . $field . '" = :' . $field;
            }
        }
        $statement = $this->db->prepare('UPDATE books SET ' . implode(',', $sets) . ' WHERE id = :id AND archived = 0');
        $statement->execute($data);
        if (!$statement->rowCount()) {
            throw new DomainException('Livro não encontrado ou arquivado.');
        }
    }

    public function find(int $id): array
    {
        $statement = $this->db->prepare('SELECT * FROM books WHERE id = ?');
        $statement->execute([$id]);
        return $statement->fetch() ?: throw new DomainException('Livro não encontrado.');
    }

    public function move(int $id, array $input): void
    {
        $type = $this->text($input, 'type', 'Tipo', 10, true);
        $reason = $this->text($input, 'reason', 'Motivo', 50, true);
        if (!isset(self::REASONS[$type]) || !in_array($reason, self::REASONS[$type], true)) {
            throw new DomainException('Selecione um tipo e um motivo válidos para a movimentação.');
        }
        $quantity = self::integer($input['quantity'] ?? '', 'Quantidade', 1);
        $note = $this->text($input, 'note', 'Observação', 500);
        $this->db->beginTransaction();
        try {
            $this->applyMovement($id, $type, $quantity, $reason, $note);
            $this->db->commit();
        } catch (\Throwable $error) {
            $this->db->rollBack();
            throw $error;
        }
    }

    private function applyMovement(int $id, string $type, int $quantity, string $reason, string $note): void
    {
        $delta = $type === 'entrada' ? $quantity : -$quantity;
        // A alteração condicional e o histórico compartilham a mesma transação.
        $statement = $this->db->prepare('UPDATE books SET quantity = quantity + :delta, updated_at = :now WHERE id = :id AND archived = 0 AND quantity + :delta BETWEEN 0 AND 1000000');
        $statement->execute(['delta' => $delta, 'now' => date('Y-m-d H:i:s'), 'id' => $id]);
        if (!$statement->rowCount()) {
            throw new DomainException('Saldo insuficiente, limite de estoque excedido ou livro indisponível.');
        }
        $balance = $this->find($id)['quantity'];
        $this->db->prepare('INSERT INTO movements (book_id,type,quantity,balance_after,reason,note,created_at) VALUES (?,?,?,?,?,?,?)')
            ->execute([$id, $type, $quantity, $balance, $reason, $note, date('Y-m-d H:i:s')]);
    }

    public function archive(int $id, bool $archive): void
    {
        $statement = $this->db->prepare('UPDATE books SET archived = :archived, updated_at = :now WHERE id = :id AND (:archived = 0 OR quantity = 0)');
        $statement->execute(['archived' => (int) $archive, 'now' => date('Y-m-d H:i:s'), 'id' => $id]);
        if (!$statement->rowCount()) {
            throw new DomainException('Só é possível arquivar livros com estoque zerado.');
        }
    }

    public function search(string $query = '', string $status = '', string $category = '', int $page = 1): array
    {
        $where = ['archived = :archived'];
        $params = ['archived' => (int) ($status === 'archived')];
        if ($query !== '') {
            $where[] = "(instr(fold(title || ' ' || author || ' ' || isbn || ' ' || location), :query) > 0 OR CAST(id AS TEXT) = :query)";
            $params['query'] = mb_strtolower($query, 'UTF-8');
        }
        if ($category !== '') { $where[] = 'category = :category'; $params['category'] = $category; }
        if ($status === 'low') { $where[] = 'quantity <= minimum_stock'; }
        if ($status === 'zero') { $where[] = 'quantity = 0'; }
        if ($status === 'available') { $where[] = 'quantity > 0'; }
        $sql = ' FROM books WHERE ' . implode(' AND ', $where);
        $count = $this->db->prepare('SELECT COUNT(*)' . $sql);
        $count->execute($params);
        $total = (int) $count->fetchColumn();
        $pages = max(1, (int) ceil($total / 20));
        $page = max(1, min($page, $pages));
        $statement = $this->db->prepare('SELECT *' . $sql . ' ORDER BY title COLLATE NOCASE, id LIMIT 20 OFFSET ' . (($page - 1) * 20));
        $statement->execute($params);
        return ['books' => $statement->fetchAll(), 'total' => $total, 'page' => $page, 'pages' => $pages];
    }

    public function categories(): array
    {
        return $this->db->query("SELECT DISTINCT category FROM books WHERE category <> '' ORDER BY category")->fetchAll(PDO::FETCH_COLUMN);
    }

    public function summary(): array
    {
        return $this->db->query('SELECT COUNT(*) AS titles, COALESCE(SUM(quantity),0) AS units, COALESCE(SUM(quantity * price_cents),0) AS value, COALESCE(SUM(quantity <= minimum_stock),0) AS low FROM books WHERE archived = 0')->fetch();
    }

    public function history(?int $bookId = null, int $page = 1): array
    {
        $where = $bookId === null ? '' : ' WHERE m.book_id = ?';
        $params = $bookId === null ? [] : [$bookId];
        $count = $this->db->prepare('SELECT COUNT(*) FROM movements m' . $where);
        $count->execute($params);
        $total = (int) $count->fetchColumn();
        $pages = max(1, (int) ceil($total / 20));
        $page = max(1, min($page, $pages));
        $statement = $this->db->prepare('SELECT m.*, b.title FROM movements m JOIN books b ON b.id = m.book_id' . $where . ' ORDER BY m.id DESC LIMIT 20 OFFSET ' . (($page - 1) * 20));
        $statement->execute($params);
        return ['movements' => $statement->fetchAll(), 'total' => $total, 'page' => $page, 'pages' => $pages];
    }
}
