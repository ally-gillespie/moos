<?php

// Add the 'staff.session' alias to the withMiddleware() call in your
// project's bootstrap/app.php (Laravel 11+ structure):

->withMiddleware(function (Middleware $middleware) {
    $middleware->alias([
        'staff.session' => \App\Http\Middleware\RequireStaffSession::class,
    ]);
})

// Also register the broadcasting auth route. In routes/web.php or a
// service provider's boot() method:
//
//   use Illuminate\Support\Facades\Broadcast;
//   Broadcast::routes(['middleware' => ['web']]);
//
// IMPORTANT: do NOT use the default Broadcast::routes() with no argument —
// it applies the 'auth' middleware, which expects Laravel's normal
// Auth::check() (e.g. a logged-in User model). This app uses a lightweight
// staff PIN + session instead of Laravel's Auth system, so the channel
// authorization itself (routes/channels.php) checks session('staff_id')
// directly. Using plain 'web' middleware here (not 'auth') lets that
// check run without requiring an Auth::user().
