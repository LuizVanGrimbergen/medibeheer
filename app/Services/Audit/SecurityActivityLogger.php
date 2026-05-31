<?php

declare(strict_types=1);

namespace App\Services\Audit;

use App\Enums\SecurityActivityDescription;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Throwable;

final class SecurityActivityLogger
{
    public function record(
        SecurityActivityDescription $description,
        ?User $causer = null,
        ?Model $subject = null,
        array $properties = [],
    ): void {
        $builder = activity(ActivityLogName::SECURITY)
            ->withProperties($this->mergeRequestContext($properties));

        if ($causer !== null) {
            $builder->causedBy($causer);
        }

        if ($subject !== null) {
            $builder->performedOn($subject);
        }

        $builder->log($description->value);
    }

    public function recordAccessDenied(
        Request $request,
        SecurityActivityDescription $description,
        ?Throwable $exception = null,
    ): void {
        $properties = [
            'route_name' => $request->route()?->getName(),
            'method' => $request->method(),
        ];

        if ($exception instanceof AuthorizationException) {
            $response = $exception->response();

            if ($response !== null && $response->code() !== null) {
                $properties['ability'] = $response->code();
            }
        }

        $user = $request->user();

        if ($user instanceof User) {
            $properties['public_id'] = $user->public_id;
            $properties['role'] = $user->role->value;
        }

        $this->record(
            $description,
            causer: $user instanceof User ? $user : null,
            properties: $properties,
        );
    }

    private function mergeRequestContext(array $properties): array
    {
        if (app()->runningInConsole()) {
            return $properties;
        }

        $request = app(Request::class);

        if ($request->ip() !== null) {
            $properties['ip'] = $request->ip();
        }

        return $properties;
    }
}
