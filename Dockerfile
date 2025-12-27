# syntax=docker/dockerfile:1
ARG PHP_VERSION=8.5.1
FROM php:${PHP_VERSION}-cli-alpine AS base
COPY --from=mlocati/php-extension-installer /usr/bin/install-php-extensions /usr/local/bin/
RUN install-php-extensions zip xdebug @composer
WORKDIR /app

FROM base AS runner
CMD ["php", "-v"]
