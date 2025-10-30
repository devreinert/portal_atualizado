Portal de Compras - Backend (PHP MVC)
---------------------------------------------
Como executar:
1. Crie um banco de dados MySQL chamado 'portal_compras'
2. Execute o seguinte SQL:

CREATE TABLE usuarios (
  id INT AUTO_INCREMENT PRIMARY KEY,
  email VARCHAR(100) NOT NULL UNIQUE,
  senha VARCHAR(255) NOT NULL,
  criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

3. Abra o VSCode, vá em: PHP Server: Serve Project
4. Acesse: http://localhost:3000/public/login.php

Pronto! O sistema de login e registro estará funcionando.
