# Sistema de Gestão Hospitalar

Sistema de gestão hospitalar desenvolvido em Laravel para gerenciamento de pacientes, profissionais de saúde e agendamento de consultas.

## Requisitos

- PHP >= 8.4.5
- Composer
- MySQL
- Node.js e NPM (para compilar assets)

## Instalação

1. **Clone o repositório**
   ```bash
   git clone https://github.com/seu-usuario/sistema-de-gestao-hospitalar.git
   cd sistema-de-gestao-hospitalar
   ```

2. **Instale as dependências do PHP**
   ```bash
   composer install
   ```

3. **Configure o ambiente**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Configure o banco de dados**
   - Crie um banco de dados MySQL
   - Atualize o arquivo `.env` com as credenciais do banco de dados

5. **Execute as migrações e seeders**
   ```bash
   php artisan migrate --seed
   ```

6. **Instale e compile os assets**
   ```bash
   npm install
   npm run build
   ```

7. **Inicie o servidor**
   ```bash
   php artisan serve
   ```

## Documentação da API

A documentação completa da API está disponível em [API_DOCUMENTATION.md](API_DOCUMENTATION.md).

### Rotas da API

#### Autenticação
- `POST /pacientes/register` - Cadastro de paciente
- `POST /pacientes/login` - Login de paciente
- `POST /profissionais/register` - Cadastro de profissional
- `POST /profissionais/login` - Login de profissional

#### Rotas Protegidas (requer autenticação)
- `POST /consultas/agendar` - Agendar consulta
- `POST /consultas/cancelar/{id}` - Cancelar consulta
- `GET /consultas` - Listar consultas
- `GET /paciente/historico` - Histórico do paciente
- `GET /profissional/agenda` - Agenda do profissional

## Testes

Para executar os testes:

```bash
php artisan test
```

## Licença

O projeto está licenciado sob a [licença MIT](LICENSE).

---

Desenvolvido por [Luccas Duarte](mailto:luccasdm.dev@gmail.com)