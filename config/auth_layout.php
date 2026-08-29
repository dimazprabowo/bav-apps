<?php

/*
|--------------------------------------------------------------------------
| Authentication Layout Configuration
|--------------------------------------------------------------------------
|
| Configuration for the guest/authentication page layout (login, register,
| forgot-password, reset-password, verify-email, confirm-password).
|
| Position controls where the authentication form panel is placed on
| desktop viewports (>= lg). Mobile always stacks vertically.
|
|   left   -> branding panel on the left,  form on the right
|   right  -> form on the left,            branding panel on the right
|   center -> branding panel hidden,       form centered (full width)
|
| Invalid values fall back to "center" so the UI never breaks.
|
*/

return [

    'position' => env('LOGIN_POSITION', 'center'),

];
