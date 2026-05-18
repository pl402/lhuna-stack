<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    public function register()
    {
        $this->reportable(function (Throwable $e) {
            //
        });

        $this->renderable(function (Throwable $e, $request) {
            if ($this->isHttpException($e)) {
                $status = $e->getStatusCode();
                
                if (in_array($status, [400, 401, 403, 404, 405, 408, 419, 429, 500, 502, 503, 504])) {
                    return \Inertia\Inertia::render('Error', [
                        'status' => $status,
                    ])->toResponse($request)->setStatusCode($status);
                }
            }
        });
    }
}
