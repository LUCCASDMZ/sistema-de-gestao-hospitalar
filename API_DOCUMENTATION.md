# Documentação da API

## Autenticação

Todas as rotas, exceto as de login e registro, requerem autenticação via token Bearer.

## Rotas Públicas

### 1. Cadastro de Paciente
- **Método**: `POST`
- **Rota**: `/api/pacientes/register`
- **Descrição**: Cria um novo cadastro de paciente
- **Corpo da Requisição**:
  ```json
  {
    "nome": "Nome do Paciente",
    "email": "paciente@exemplo.com",
    "cpf": "123.456.789-00",
    "password": "senha123",
    "telefone": "(11) 99999-9999",
    "endereco": "Rua Exemplo, 123",
    "data_nascimento": "1990-01-01",
    "sexo": "M",
    "estado_civil": "solteiro",
    "profissao": "Analista"
  }
  ```
- **Resposta de Sucesso (201)**:
  ```json
  {
    "message": "Paciente cadastrado com sucesso",
    "paciente": {
      "id": 1,
      "nome": "Nome do Paciente",
      "email": "paciente@exemplo.com"
      // outros campos...
    }
  }
  ```

### 2. Login de Paciente
- **Método**: `POST`
- **Rota**: `/api/pacientes/login`
- **Descrição**: Autentica um paciente e retorna um token de acesso
- **Corpo da Requisição**:
  ```json
  {
    "email": "paciente@exemplo.com",
    "password": "senha123"
  }
  ```
- **Resposta de Sucesso (200)**:
  ```json
  {
    "token": "1|abcdefghijklmnopqrstuvwxyz",
    "user": {
      "id": 1,
      "nome": "Nome do Paciente",
      "email": "paciente@exemplo.com"
    }
  }
  ```

### 3. Cadastro de Profissional
- **Método**: `POST`
- **Rota**: `/api/profissionais/register`
- **Descrição**: Cria um novo cadastro de profissional de saúde
- **Corpo da Requisição**:
  ```json
  {
    "nome": "Dr. Profissional",
    "email": "profissional@exemplo.com",
    "especialidade": "Cardiologia",
    "password": "senha123"
  }
  ```
- **Resposta de Sucesso (201)**:
  ```json
  {
    "message": "Profissional cadastrado com sucesso",
    "profissional": {
      "id": 1,
      "nome": "Dr. Profissional",
      "email": "profissional@exemplo.com",
      "especialidade": "Cardiologia"
    }
  }
  ```

### 4. Login de Profissional
- **Método**: `POST`
- **Rota**: `/api/profissionais/login`
- **Descrição**: Autentica um profissional e retorna um token de acesso
- **Corpo da Requisição**:
  ```json
  {
    "email": "profissional@exemplo.com",
    "password": "senha123"
  }
  ```
- **Resposta de Sucesso (200)**:
  ```json
  {
    "token": "2|abcdefghijklmnopqrstuvwxyz",
    "user": {
      "id": 1,
      "nome": "Dr. Profissional",
      "email": "profissional@exemplo.com",
      "especialidade": "Cardiologia"
    }
  }
  ```

## Rotas Protegidas

Todas as rotas abaixo requerem o token de autenticação no cabeçalho:
```
Authorization: Bearer {token}
```

### 5. Agendar Consulta
- **Método**: `POST`
- **Rota**: `/api/consultas/agendar`
- **Descrição**: Agenda uma nova consulta
- **Corpo da Requisição**:
  ```json
  {
    "profissional_id": 1,
    "data_hora": "2025-05-10 14:30:00",
    "tipo_consulta": "Rotina"
  }
  ```
- **Resposta de Sucesso (201)**:
  ```json
  {
    "message": "Consulta agendada com sucesso",
    "consulta": {
      "id": 1,
      "paciente_id": 1,
      "profissional_id": 1,
      "data_hora": "2025-05-10 14:30:00",
      "tipo_consulta": "Rotina",
      "status": "agendada"
    }
  }
  ```

### 6. Cancelar Consulta
- **Método**: `POST`
- **Rota**: `/api/consultas/cancelar/{id}`
- **Descrição**: Cancela uma consulta existente
- **Parâmetros de URL**:
  - `id` (obrigatório): ID da consulta
- **Resposta de Sucesso (200)**:
  ```json
  {
    "message": "Consulta cancelada com sucesso"
  }
  ```

### 7. Listar Consultas
- **Método**: `GET`
- **Rota**: `/api/consultas`
- **Descrição**: Lista todas as consultas do usuário autenticado
- **Resposta de Sucesso (200)**:
  ```json
  [
    {
      "id": 1,
      "paciente_id": 1,
      "profissional_id": 1,
      "data_hora": "2025-05-10 14:30:00",
      "tipo_consulta": "Rotina",
      "status": "agendada",
      "created_at": "2025-05-01T10:00:00.000000Z",
      "updated_at": "2025-05-01T10:00:00.000000Z"
    }
  ]
  ```

### 8. Histórico do Paciente
- **Método**: `GET`
- **Rota**: `/api/paciente/historico`
- **Descrição**: Retorna o histórico de consultas do paciente autenticado
- **Resposta de Sucesso (200)**:
  ```json
  [
    {
      "id": 1,
      "data_hora": "2025-05-10 14:30:00",
      "tipo_consulta": "Rotina",
      "status": "realizada",
      "profissional": {
        "nome": "Dr. Profissional",
        "especialidade": "Cardiologia"
      }
    }
  ]
  ```

### 9. Agenda do Profissional
- **Método**: `GET`
- **Rota**: `/api/profissional/agenda`
- **Descrição**: Retorna a agenda do profissional autenticado
- **Resposta de Sucesso (200)**:
  ```json
  [
    {
      "id": 1,
      "data_hora": "2025-05-10 14:30:00",
      "tipo_consulta": "Rotina",
      "status": "agendada",
      "paciente": {
        "nome": "Nome do Paciente",
        "telefone": "(11) 99999-9999"
      }
    }
  ]
  ```

## Códigos de Resposta

- `200` - Requisição bem-sucedida
- `201` - Recurso criado com sucesso
- `400` - Dados inválidos
- `401` - Não autorizado
- `403` - Acesso negado
- `404` - Recurso não encontrado
- `422` - Erro de validação
- `500` - Erro interno do servidor

## Validações

- CPF deve ser válido e único
- E-mail deve ser válido e único
- Senha deve ter no mínimo 6 caracteres
- Data de nascimento deve ser uma data válida
- Telefone deve estar no formato (XX) XXXXX-XXXX
- Data/hora da consulta deve ter pelo menos 24 horas de antecedência
- Um profissional não pode ter mais de uma consulta no mesmo horário
- Um paciente não pode ter mais de uma consulta no mesmo horário
