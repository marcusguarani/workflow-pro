CREATE DATABASE IF NOT EXISTS workflow_sede_obras CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE workflow_sede_obras;

CREATE TABLE IF NOT EXISTS departments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    kind VARCHAR(40) NOT NULL DEFAULT 'Sede',
    created_at DATETIME NOT NULL
);

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    department_id INT NULL,
    name VARCHAR(120) NOT NULL,
    email VARCHAR(160) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role VARCHAR(60) NOT NULL DEFAULT 'Administrador',
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL,
    CONSTRAINT fk_users_department FOREIGN KEY (department_id) REFERENCES departments(id)
);

CREATE TABLE IF NOT EXISTS demands (
    id INT AUTO_INCREMENT PRIMARY KEY,
    origin_department_id INT NOT NULL,
    destination_department_id INT NOT NULL,
    responsible_user_id INT NULL,
    created_by INT NOT NULL,
    title VARCHAR(180) NOT NULL,
    description TEXT NOT NULL,
    requester_name VARCHAR(120) NOT NULL,
    type VARCHAR(80) NOT NULL DEFAULT 'Solicitação',
    priority VARCHAR(40) NOT NULL DEFAULT 'Média',
    status VARCHAR(40) NOT NULL DEFAULT 'Aberto',
    due_date DATE NOT NULL,
    completed_at DATETIME NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    CONSTRAINT fk_demands_origin_department FOREIGN KEY (origin_department_id) REFERENCES departments(id),
    CONSTRAINT fk_demands_destination_department FOREIGN KEY (destination_department_id) REFERENCES departments(id),
    CONSTRAINT fk_demands_responsible_user FOREIGN KEY (responsible_user_id) REFERENCES users(id),
    CONSTRAINT fk_demands_created_by FOREIGN KEY (created_by) REFERENCES users(id)
);

CREATE TABLE IF NOT EXISTS demand_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    demand_id INT NOT NULL,
    user_id INT NOT NULL,
    action VARCHAR(120) NOT NULL,
    notes TEXT NULL,
    created_at DATETIME NOT NULL,
    CONSTRAINT fk_history_demand FOREIGN KEY (demand_id) REFERENCES demands(id) ON DELETE CASCADE,
    CONSTRAINT fk_history_user FOREIGN KEY (user_id) REFERENCES users(id)
);
