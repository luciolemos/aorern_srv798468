#!/bin/bash

# Script para aplicar PermissionMiddleware a todos os controllers Admin
# Este é um guia de aplicação manual - ajuste conforme necessário

echo "=== Aplicando Permissões aos Controllers ==="

# Controllers e seus CRUD methods
declare -A CONTROLLERS=(
    ["PostCategories"]="create:post_categories:create store:post_categories:create edit:post_categories:edit update:post_categories:edit delete:post_categories:delete"
    ["Usuarios"]="store:users:create edit:users:edit update:users:edit delete:users:delete ativar:users:approve desativar:users:block"
    ["Equipamentos"]="salvar:equipamentos:create atualizar:equipamentos:edit deletar:equipamentos:delete"
    ["Pessoal"]="salvar:pessoal:create atualizar:pessoal:edit deletar:pessoal:delete"
    ["Obras"]="salvar:obras:create atualizar:obras:edit deletar:obras:delete"
    ["Funcoes"]="salvar:funcoes:create atualizar:funcoes:edit deletar:funcoes:delete"
    ["Categorias"]="salvar:categorias:create atualizar:categorias:edit deletar:categorias:delete"
)

# Padrão:
# Para cada controller adicione no constructor:
# use App\Middleware\PermissionMiddleware;

# E em cada método CRUD:
# PermissionMiddleware::authorize('recurso:acao');

echo "Leia o arquivo PERMISSOES_ACL.md para entender a estrutura"
echo ""
echo "Próximas ações manual:"
echo "1. PostCategoriesController - add 'post_categories:*' checks"
echo "2. UsuariosController - add 'users:*' checks"
echo "3. EquipamentosController - add 'equipamentos:*' checks"
echo "4. PessoalController - add 'pessoal:*' checks"
echo "5. ObrasController - add 'obras:*' checks"
echo "6. FuncoesController - add 'funcoes:*' checks"
echo ""
echo "Padrão para adicionar em cada method:"
echo "  PermissionMiddleware::authorize('recurso:acao');"
