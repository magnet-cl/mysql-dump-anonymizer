FROM composer:1 AS build-env
COPY composer.* /build/
WORKDIR /build
RUN composer install --no-dev
COPY . /build

FROM php:7.4-cli-alpine
COPY --from=build-env /build /anonymizer
ENTRYPOINT ["php", "/anonymizer/bin/mysql-dump-anonymize.php"]
CMD ["--show-progress=0", "--config=/anonymizer/sample/anon.yml", "</anonymizer/sample/sample.sql", ">/anonymizer/sample/anon_result.sql"]
