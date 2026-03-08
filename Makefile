.SHELLFLAGS := -e -o pipefail -c
SHELL := /bin/bash

.PHONY: test-unit test-ci quality quality-full ci-local ci-local-full preflight install-hooks release-check release-tag

BASE_URL ?= https://127.0.0.1/aorern
FULL_SMOKE_URL ?=
LOCAL_BASE_URL ?= https://127.0.0.1/aorern

test-unit:
	bash scripts/run_quality_gate.sh --unit-only

preflight:
	bash scripts/preflight.sh

install-hooks:
	bash scripts/install-git-hooks.sh

release-check:
	BASE_URL=$(LOCAL_BASE_URL) FULL_SMOKE_URL=$(LOCAL_BASE_URL) bash scripts/release_check.sh

release-tag:
	@if [ -z "$(VERSION)" ]; then echo "Uso: make release-tag VERSION=vX.Y.Z"; exit 1; fi
	VERSION=$(VERSION) BASE_URL=$(LOCAL_BASE_URL) FULL_SMOKE_URL=$(LOCAL_BASE_URL) bash scripts/release_tag.sh

test-ci:
	bash scripts/run_quality_gate.sh --base-url $(BASE_URL) --skip-phpunit

quality:
	bash scripts/run_quality_gate.sh --base-url $(BASE_URL)

quality-full:
	bash scripts/run_quality_gate.sh --base-url $(BASE_URL) --full-smoke-url $(FULL_SMOKE_URL)

ci-local:
	$(MAKE) test-ci BASE_URL=$(LOCAL_BASE_URL)

ci-local-full:
	$(MAKE) quality-full BASE_URL=$(LOCAL_BASE_URL) FULL_SMOKE_URL=$(LOCAL_BASE_URL)
