# 🚗 HeyCar

<img width="489" height="512" alt="ok1" src="https://github.com/user-attachments/assets/4e47feb8-1aad-4169-bd22-cf1f9736cd4f" />
<img width="489" height="512" alt="ok2" src="https://github.com/user-attachments/assets/07c1ca1a-a83e-4dfd-a687-1cd1224ee11f" />
<img width="489" height="512" alt="ok3" src="https://github.com/user-attachments/assets/19e9e1a4-6422-44a6-bef6-0a6e48c0f0ce" />
<img width="342" height="573" alt="ok4" src="https://github.com/user-attachments/assets/cb14bfde-95f0-461e-8ff4-48a950bb0ef7" />


# HeyCar 🚗

**HeyCar** é um sistema em **PHP** para organizar caronas e transporte colaborativo.  
Ele permite **cadastrar caronas**, **gerenciar rotas e participantes**, e **controlar transações** ligadas ao transporte.  
O projeto utiliza **MySQL** como banco de dados, com scripts SQL já incluídos.

---

## 📂 Estrutura do Projeto
- `caronas/`, `caronas2/`, `caronas3/` → versões/módulos do sistema  
- `transacao_aura_transporte.php` → lógica de transações  
- `u839226731_meutrator.sql` → script para criar o banco de dados  
- `index.php` → ponto de entrada da aplicação  

---

## ▶️ Como Rodar o Código

1. **Instale um servidor local**  
   - Pode usar **XAMPP**, **Laragon** ou **Docker** com PHP + MySQL.

2. **Clone o repositório**  
   ```bash
   git clone https://github.com/pautz/HeyCar.git
Coloque os arquivos na pasta do servidor

Exemplo: htdocs/HeyCar no XAMPP.

Crie o banco de dados

Acesse o phpMyAdmin ou MySQL CLI.

Importe o arquivo u839226731_meutrator.sql.

Configure a conexão

Verifique nos arquivos .php as credenciais de conexão (host, user, password, dbname).

Ajuste conforme seu ambiente.

Abra no navegador

Digite: http://localhost/HeyCar/index.php
