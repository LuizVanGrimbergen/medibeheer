<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Privacy\UserDataExportService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportUserDataController extends Controller
{
    public function __construct(
        private readonly UserDataExportService $userDataExportService,
    ) {}

    public function __invoke(Request $request): StreamedResponse
    {
        $user = $request->user();

        if (! $user instanceof User) {
            abort(403);
        }

        $this->authorize('exportData', $user);

        $payload = $this->userDataExportService->exportForUser($user);
        $filename = sprintf('medibeheer-export-%s.json', $user->public_id);

        return response()->streamDownload(
            function () use ($payload): void {
                echo json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            },
            $filename,
            [
                'Content-Type' => 'application/json',
            ],
        );
    }
}
