# Hub Inteligente de Recursos Educacionais


Repositório da **solução completa** para o desafio técnico de desenvolvimento de um **Hub Inteligente de Recursos Educacionais**. A aplicação permite gerenciamento de recursos educacionais (PDFs, vídeos, etc.) e **Inteligência Artificial** (Google Gemini) para sugestões automáticas.

## Arquitetura e Stack

| Componente | Tecnologias |
|------------|-------------|
| **Backend** | Laravel 10.x, PHP 8.x, Composer, Eloquent ORM, Artisan |
| **Frontend** | Angular 21.x, TypeScript, Angular CLI, Vitest |
| **Banco** | PostgreSQL (Neon DB) |
| **IA** | Google Gemini API |
| **Deploy** | Render (backend), Vercel (frontend) |
| **Outros** | Vite (assets), PHPUnit (tests backend), Docker/Nginx |

## Estrutura do Repositório

```
Processo-seletivo-Vlab/
├── backend/          # API Laravel
│   ├── app/          # Models, Controllers, Routes
│   ├── database/     # Migrations, Seeders
│   ├── .env.example  # Config de ambiente
│   ├── Dockerfile    # Containerização
│   └── README.md     # Docs Laravel
└── frontend/         # App Angular
    ├── src/          # Components, Services, Models
    ├── angular.json  # Config Angular
    ├── package.json  # Scripts/Deps
    └── README.md     # Docs Angular
```

## Pré-requisitos

- **Backend**: PHP 8.2+, Composer, (opcional) Docker/Node para Vite.
- **Frontend**: Node.js 20+, npm.
- **Banco de Dados**: Conta no [Neon DB](https://neon.tech) (PostgreSQL serverless).
- **API de IA**: Chave da [Google Gemini API](https://ai.google.dev/).
- Git clonado: `git clone https://github.com/M-msdias/Processo-seletivo-Vlab.git`

## Instalação e Execução (Local)

### Backend (API - http://localhost:8000)
1. `cd backend`
2. `cp .env.example .env`
3. Edite `.env`
4. `composer install --optimize-autoloader --no-dev`
5. `php artisan migrate`
6. `php artisan serve`

### Frontend (App - http://localhost:4200)
1. `cd frontend`
2. `npm install`
3. Configure `src/environments/environment.ts` com URL da API: `apiUrl: 'http://localhost:8000/api'`
4. `ng serve`


## Testes

- **Backend**: `cd backend && php artisan test` (PHPUnit).
- **Frontend**: `cd frontend && ng test` (Vitest).

## Deploy

- **Backend**: [Render](https://render.com) - Deploy automático via repositório Git. Configure variáveis de ambiente no painel Render (`APP_KEY`, `DB_*`, etc.).
- **Frontend**: [Vercel](https://vercel.com) - Deploy automático via `vercel.json`. Configure variável de ambiente `API_URL` apontando para a URL do backend no Render.

## Demonstração

- API Docs: https://processo-seletivo-vlab-1.onrender.com/api/documentation
- Frontend Live: https://processo-seletivo-vlab.vercel.app/

## Próximos Passos / Melhorias

- Integração CI/CD (GitHub Actions).
