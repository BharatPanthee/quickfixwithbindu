# Bundled verifier

`php-jwt/` contains firebase/php-jwt v7.2.1 from https://github.com/googleapis/php-jwt/tree/v7.2.1, under its included BSD-3-Clause license. Its `Firebase\JWT` namespace is changed to `QuickFix\Vendor\Firebase\JWT` to isolate it from other installed plugins. No other source changes are applied.

Only RS256 is accepted by QuickFix. Firebase issuer, audience, lifetime, UID, authentication time, identity disablement and revocation checks are implemented in the integration layer. App Check uses its separate issuer, project number, app ID allowlist and JWKS.

The distributable includes the dependency; installation requires no Composer command. Rebuild from a reviewed upstream release when updating the verifier.
