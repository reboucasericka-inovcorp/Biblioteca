<?php

namespace App\Services;

use App\Models\Book;
use App\Models\Requisition;
use Illuminate\Support\Collection;

/**
 * Análise de stock vs requisições active/late (lógica partilhada entre comandos e API admin).
 */
class StockReconciliationService
{
    /**
     * @return Collection<int, Book> livros com atributo out_on_loan_count
     */
    public function booksWithLoanCounts(): Collection
    {
        return Book::query()
            ->withCount([
                'requisitions as out_on_loan_count' => fn ($q) => $q->whereIn('status', [
                    Requisition::STATUS_ACTIVE,
                    Requisition::STATUS_LATE,
                ]),
            ])
            ->orderBy('id')
            ->get();
    }

    /**
     * Linhas aplicáveis na reconciliação (legado: stock contava exemplares ainda emprestados).
     *
     * @param  bool  $onlyUnreconciled  se true, ignora livros já marcados com stock_reconciled
     * @return array{apply: list<array{id:int,name:string,out:int,antes:int,depois:int}>, suspicious: list<array{id:int,name:string,out:int,stock:int}>, skipped_reconciled: int}
     */
    public function buildReconciliationPlan(bool $onlyUnreconciled = true): array
    {
        $apply = [];
        $suspicious = [];
        $skippedReconciled = 0;

        foreach ($this->booksWithLoanCounts() as $book) {
            if ($onlyUnreconciled && $book->stock_reconciled) {
                $skippedReconciled++;

                continue;
            }

            $out = (int) $book->out_on_loan_count;
            $current = (int) $book->stock;

            if ($out === 0) {
                continue;
            }

            if ($current < $out) {
                $suspicious[] = [
                    'id' => $book->id,
                    'name' => mb_substr((string) $book->name, 0, 60),
                    'out' => $out,
                    'stock' => $current,
                ];

                continue;
            }

            $new = max(0, $current - $out);
            if ($new === $current) {
                continue;
            }

            $apply[] = [
                'id' => $book->id,
                'name' => mb_substr((string) $book->name, 0, 60),
                'out' => $out,
                'antes' => $current,
                'depois' => $new,
            ];
        }

        return [
            'apply' => $apply,
            'suspicious' => $suspicious,
            'skipped_reconciled' => $skippedReconciled,
        ];
    }

    /**
     * Inconsistências: stock registado inferior ao número de empréstimos em curso.
     *
     * @return list<array{id:int,title:string,stock:int,out_on_loan:int,diff:int}>
     */
    public function inconsistencies(): array
    {
        $list = [];

        foreach ($this->booksWithLoanCounts() as $book) {
            $out = (int) $book->out_on_loan_count;
            $stock = (int) $book->stock;

            if ($stock < $out) {
                $list[] = [
                    'id' => $book->id,
                    'title' => (string) $book->name,
                    'stock' => $stock,
                    'out_on_loan' => $out,
                    'diff' => $out - $stock,
                ];
            }
        }

        return $list;
    }
}
