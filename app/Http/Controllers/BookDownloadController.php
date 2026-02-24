<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Requisition;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class BookDownloadController extends Controller
{
    /**
     * Download protegido do PDF.
     * Apenas Admin ou utilizador com requisição ativa pode fazer download.
     */
    public function download(Request $request, Book $book)
    {
        if (!$book->file_path) {
            abort(404, 'Este livro não possui PDF disponível.');
        }

        $user = $request->user();

        if ($user->hasRole('Admin')) {
            return $this->downloadFile($book);
        }

        $hasActiveRequisition = $book->requisitions()
            ->where('user_id', $user->id)
            ->where('status', Requisition::STATUS_ACTIVE)
            ->exists();

        if (!$hasActiveRequisition) {
            abort(403, 'Não tem permissão para fazer download deste livro.');
        }

        return $this->downloadFile($book);
    }

    private function downloadFile(Book $book): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        $path = Storage::disk('public')->path($book->file_path);

        if (!file_exists($path)) {
            abort(404, 'Ficheiro PDF não encontrado.');
        }

        $name = Str::slug($book->name) . '.pdf';

        return response()->download($path, $name, [
            'Content-Type' => 'application/pdf',
        ]);
    }
}
