-- Mikhmon Custom Database Schema
-- Database: mikhmon_agent

-- Tabel Agen
CREATE TABLE IF NOT EXISTS agents (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    name VARCHAR(100) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    balance INT DEFAULT 0,
    is_active TINYINT DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabel Transaksi Voucher
CREATE TABLE IF NOT EXISTS voucher_transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    agent_id INT NOT NULL,
    session_name VARCHAR(50) NOT NULL,
    profile VARCHAR(100) NOT NULL,
    username VARCHAR(100) NOT NULL,
    password VARCHAR(100) NOT NULL,
    customer_phone VARCHAR(20),
    price INT DEFAULT 0,
    wa_sent TINYINT DEFAULT 0,
    wa_sent_at DATETIME NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (agent_id) REFERENCES agents(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Konfigurasi Global (key-value store)
CREATE TABLE IF NOT EXISTS settings (
    `key` VARCHAR(50) PRIMARY KEY,
    value TEXT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabel Harga Kustom Agen
CREATE TABLE IF NOT EXISTS agent_prices (
    id INT AUTO_INCREMENT PRIMARY KEY,
    agent_id INT NOT NULL,
    profile VARCHAR(100) NOT NULL,
    buy_price INT DEFAULT 0,
    sell_price INT DEFAULT 0,
    data_limit INT DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (agent_id) REFERENCES agents(id) ON DELETE CASCADE,
    UNIQUE(agent_id, profile)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabel Penagihan Mandiri
CREATE TABLE IF NOT EXISTS billing (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    amount INT DEFAULT 0,
    due_date INT NOT NULL, -- Hari jatuh tempo (1-31)
    description TEXT,
    last_reminded_at DATETIME NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Default settings
INSERT IGNORE INTO settings (`key`, value) VALUES
('vc_mode', 'sama'),
('vc_char', 'kombinasi'),
('vc_len', '8'),
('vc_prefix', ''),
('vc_ucase', '1'),
('fonnte_token', ''),
('fonnte_template', 'Halo! Berikut voucher Anda:\n\nUsername: {username}\nPassword: {password}\nPaket: {profile}\nHarga: {price}\n\nTerima kasih!\n- {agent_name}'),
('fonnte_billing_template', 'Halo {username}!\n\nTagihan internet internet bulanan Anda sebesar *Rp {amount}* telah memasuki masa jatuh tempo pada tanggal *{due_date}*.\n\nMohon segera melakukan pembayaran untuk menghindari pemutusan layanan.\n\nTerima kasih.');
