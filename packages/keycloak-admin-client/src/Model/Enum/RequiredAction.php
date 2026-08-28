<?php

declare(strict_types=1);

namespace Feroz\KeycloakAdmin\Model\Enum;

enum RequiredAction: string
{
    case VERIFY_EMAIL = 'VERIFY_EMAIL';
    case UPDATE_PASSWORD = 'UPDATE_PASSWORD';
    case CONFIGURE_TOTP = 'CONFIGURE_TOTP';
    case UPDATE_PROFILE = 'UPDATE_PROFILE';
    case TERMS_AND_CONDITIONS = 'TERMS_AND_CONDITIONS';
    case WEBAUTHN_REGISTER = 'webauthn-register';
    case WEBAUTHN_REGISTER_PASSWORDLESS = 'webauthn-register-passwordless';
    case DELETE_ACCOUNT = 'delete_account';
}
