.PHONY: php_qa php_lint php_cs php_csf phpstan php_tests php_coverage python_qa

all:
	@$(MAKE) -pRrq -f $(lastword $(MAKEFILE_LIST)) : 2>/dev/null | awk -v RS= -F: '/^# File/,/^# Finished Make data base/ {if ($$1 !~ "^[#.]") {print $$1}}' | sort | egrep -v -e '^[^[:alnum:]]' -e '^$@$$' | xargs

vendor: composer.json composer.lock
	composer install

php_qa: php_lint phpstan php_cs

php_lint: vendor
	vendor/bin/linter src tests

php_cs: vendor
	vendor/bin/codesniffer src tests

php_csf: vendor
	vendor/bin/codefixer src tests

phpstan: vendor
	vendor/bin/phpstan analyse -c phpstan.neon src

php_tests: vendor
	vendor/bin/tester -s -p php --colors 1 -C tests/cases

php_coverage: vendor
	vendor/bin/tester -s -p php --colors 1 -C --coverage ./coverage.xml --coverage-src ./src tests/cases

pylint:
	python -m pip install pylint

python_qa: pylint
	pylint **/*.py
