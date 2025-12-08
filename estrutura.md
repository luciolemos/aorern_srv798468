# Estrutura do Projeto OPUS (MVC)

    mvc/
    ├── app/
    │   ├── Controllers/
    │   │   ├── Admin/
    │   │   │   ├── AuthController.php
    │   │   │   ├── DashboardController.php
    │   │   │   ├── DocsController.php
    │   │   │   ├── EquipamentosController.php
    │   │   │   ├── FuncoesController.php
    │   │   │   ├── ObrasController.php
    │   │   │   ├── OpusController.php
    │   │   │   ├── PessoalController.php
    │   │   │   ├── PostsController.php
    │   │   │   ├── StatusController.php
    │   │   │   └── SystemController.php
    │   │   └── Site/
    │   │   ├── AboutController.php
    │   │   ├── BlogController.php
    │   │   ├── ContactController.php
    │   │   ├── CoverageController.php
    │   │   ├── HomeController.php
    │   │   └── ReadmeController.php
    │   ├── Core/
    │   │   ├── App.php
    │   │   ├── Controller.php
    │   │   ├── Database.php
    │   │   ├── Router.php
    │   │   └── View.php
    │   ├── Helpers/
    │   │   ├── FormatHelper.php
    │   │   ├── RouteHelper.php
    │   │   └── SystemVersions.php
    │   ├── Models/
    │   │   ├── EquipamentoModel.php
    │   │   ├── FuncaoModel.php
    │   │   ├── ObraModel.php
    │   │   ├── PessoalModel.php
    │   │   ├── Post.php
    │   │   └── User.php
    │   └── Views/
    │   ├── 404.php
    │   ├── about.php
    │   ├── admin/
    │   │   ├── dashboard.php
    │   │   ├── documents/
    │   │   │   └── [...documentação]
    │   │   ├── equipamentos/
    │   │   │   ├── _form.php
    │   │   │   ├── cadastrar.php
    │   │   │   ├── editar.php
    │   │   │   ├── index.php
    │   │   │   └── indice.php
    │   │   ├── ferramenta.php
    │   │   ├── funcoes/
    │   │   │   └── [...views]
    │   │   ├── login.php
    │   │   ├── obras/
    │   │   │   └── [...views]
    │   │   ├── opus/
    │   │   │   └── [...legacy views]
    │   │   ├── pessoal/
    │   │   │   └── [...views]
    │   │   ├── posts/
    │   │   │   └── [...views]
    │   │   ├── status.php
    │   │   └── system/
    │   │   └── info.php, versions.php
    │   ├── blog.php
    │   ├── contact.php
    │   ├── coverage.php
    │   ├── dash.php
    │   ├── home.php
    │   ├── layouts/
    │   │   ├── admin.php
    │   │   └── [...layout fragments]
    │   ├── post.php
    │   ├── posts.sql
    │   ├── readme.php
    │   └── templates/
    │   └── main.php
    ├── config/
    │ └── config.php
    ├── public/
    │   └── assets/
    │      ├── css/
    │      ├── images/
    │      └── js/
    │      ├── admin.js
    │      └── [...scripts]
    ├── vendor/
    │ └── autoload.php
    ├── composer.json
    ├── composer.lock
    └── .env