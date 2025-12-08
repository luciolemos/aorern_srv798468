#!/bin/bash

echo "🧪 Iniciando testes com cobertura..."

rm -rf coverage/html

XDEBUG_MODE=coverage ./vendor/bin/phpunit --coverage-html coverage/html

if [ -f coverage/html/index.html ]; then
    echo "✅ Relatório gerado em coverage/html/index.html"

    if command -v xdg-open &>/dev/null; then
        xdg-open coverage/html/index.html
    elif command -v open &>/dev/null; then
        open coverage/html/index.html
    else
        echo "📝 Abra manualmente: file://$(pwd)/coverage/html/index.html"
    fi
else
    echo "❌ Relatório não foi gerado."
fi
