# 🚀 Guia de Deploy - Componentes Reutilizáveis

## ✅ Pré-Requisitos

- [ ] Todos os arquivos foram validados (sintaxe PHP)
- [ ] Testes manuais foram executados (site + admin)
- [ ] Documentação foi lida
- [ ] Checklist de validação foi seguido

## 📋 Checklist de Deploy

### 1. Backup
```bash
# Fazer backup antes de deploy
cp -r /var/www/mvc /var/www/mvc.backup.$(date +%Y%m%d)
```

### 2. Validação Final
```bash
# Validar sintaxe de todos os componentes
for file in app/Views/components/*.php; do
  php -l "$file" || echo "ERROR: $file"
done

# Validar arquivos principais
php -l public/index.php || echo "ERROR: public/index.php"
php -l app/Views/dash.php || echo "ERROR: app/Views/dash.php"
```

### 3. Permissões
```bash
# Verificar permissões
chmod 755 app/Views/components/
chmod 644 app/Views/components/*.php
chmod 644 public/assets/css/navbar-universal.css
```

### 4. Cache
```bash
# Limpar cache de OPcache (se ativado)
# Opção 1: Reiniciar PHP-FPM
sudo systemctl restart php-fpm

# Opção 2: Executar script de limpeza
php -r "opcache_reset();" 2>/dev/null
```

### 5. Testes
```bash
# Testar endpoints principais
curl -I http://mvc.local/home
curl -I http://mvc.local/admin/dashboard
curl -I http://mvc.local/blog
```

### 6. Monitoramento
- [ ] Verificar logs de erro: `tail -f /var/log/apache2/error.log`
- [ ] Verificar logs de PHP: `tail -f /var/log/php-fpm.log`
- [ ] Monitorar performance: `top`

## 📊 Rollback Plan

Se algo der errado:

```bash
# Reverter para backup
rm -rf /var/www/mvc
cp -r /var/www/mvc.backup.YYYYMMDD /var/www/mvc

# Reiniciar serviços
sudo systemctl restart apache2 php-fpm

# Limpar cache
php -r "opcache_reset();"
```

## 📈 Monitoramento Pós-Deploy

### 1ª Hora
- [ ] Verificar site público em desktop
- [ ] Verificar site público em mobile
- [ ] Verificar admin dashboard em desktop
- [ ] Verificar admin dashboard em mobile
- [ ] Testar logout (flash message)
- [ ] Testar CSRF tokens

### 24 Horas
- [ ] Verificar logs de erro
- [ ] Verificar performance
- [ ] Testar todos os menus
- [ ] Testar responsividade

### Semanal
- [ ] Análise de performance
- [ ] Revisão de logs
- [ ] Feedback de usuários

## 🎯 Checklist Final

- [ ] Componentes sincronizados (navbar, sidebar, footer)
- [ ] CSS centralizado funcionando
- [ ] Todas as páginas renderizando corretamente
- [ ] Mobile responsivo
- [ ] CSRF validação ativa
- [ ] Flash messages funcionando
- [ ] Sem erros no console do navegador
- [ ] Sem erros nos logs de PHP

## 📞 Contato em Caso de Problema

1. Consulte `VALIDATION_CHECKLIST.md`
2. Verifique logs
3. Revise `DIAGRAMA_ARQUITETURA.md`
4. Consulte documentação

---

**Status:** Pronto para deploy  
**Data:** 2024  
**Responsável:** DevOps Team
