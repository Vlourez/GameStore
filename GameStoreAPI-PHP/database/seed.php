<?php

declare(strict_types=1);

/**
 * Run this once after importing schema.sql to populate sample data,
 * including an admin account with a properly bcrypt-hashed password.
 *
 *   php database/seed.php
 */

require __DIR__ . '/../autoload.php';

use GameStore\Support\Env;
use GameStore\Support\Uuid;

Env::load(__DIR__ . '/../.env');

$host = Env::get('DB_HOST', '127.0.0.1');
$port = Env::get('DB_PORT', '3306');
$name = Env::get('DB_NAME', 'gamestore_db');
$user = Env::get('DB_USER', 'root');
$pass = Env::get('DB_PASS', '');

$pdo = new PDO(
    "mysql:host={$host};port={$port};dbname={$name};charset=utf8mb4",
    (string) $user,
    (string) $pass,
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

function seedCategory(PDO $pdo, string $name, string $description, string $slug): string
{
    $existing = $pdo->prepare('SELECT id FROM categories WHERE slug = :slug');
    $existing->execute(['slug' => $slug]);
    if ($row = $existing->fetch(PDO::FETCH_ASSOC)) {
        return $row['id'];
    }

    $id = Uuid::v4();
    $now = gmdate('Y-m-d H:i:s');
    $stmt = $pdo->prepare(
        'INSERT INTO categories (id, name, description, slug, created_at, updated_at, is_deleted)
         VALUES (:id, :name, :description, :slug, :now, NULL, 0)'
    );
    $stmt->execute(['id' => $id, 'name' => $name, 'description' => $description, 'slug' => $slug, 'now' => $now]);

    return $id;
}

function seedGame(PDO $pdo, array $g, string $categoryId): void
{
    $existing = $pdo->prepare('SELECT id, cover_image_url FROM games WHERE title = :title');
    $existing->execute(['title' => $g['title']]);
    if ($row = $existing->fetch(PDO::FETCH_ASSOC)) {
        // Jogo já existe: só completa a capa se ela ainda estiver vazia (rodar o seed de novo é seguro).
        if (($row['cover_image_url'] ?? '') === '' && !empty($g['cover_image_url'])) {
            $update = $pdo->prepare('UPDATE games SET cover_image_url = :url WHERE id = :id');
            $update->execute(['url' => $g['cover_image_url'], 'id' => $row['id']]);
        }
        return;
    }

    $now = gmdate('Y-m-d H:i:s');
    $stmt = $pdo->prepare(
        'INSERT INTO games
            (id, title, description, developer, publisher, price, discount_price, release_date,
             cover_image_url, status, tags, category_id, created_at, updated_at, is_deleted)
         VALUES
            (:id, :title, :description, :developer, :publisher, :price, :discount_price, :release_date,
             :cover_image_url, :status, :tags, :category_id, :now, NULL, 0)'
    );
    $stmt->execute([
        'id' => Uuid::v4(),
        'title' => $g['title'],
        'description' => $g['description'],
        'developer' => $g['developer'],
        'publisher' => $g['publisher'],
        'price' => $g['price'],
        'discount_price' => $g['discount_price'] ?? null,
        'release_date' => $g['release_date'],
        'cover_image_url' => $g['cover_image_url'] ?? '',
        'status' => $g['status'] ?? 2,
        'tags' => json_encode($g['tags'] ?? [], JSON_THROW_ON_ERROR),
        'category_id' => $categoryId,
        'now' => $now,
    ]);
}

/** Capa oficial do jogo na loja da Steam (formato 616x353, perto do 16:9 do card). */
function steamCover(int $appId): string
{
    return "https://shared.akamai.steamstatic.com/store_item_assets/steam/apps/{$appId}/capsule_616x353.jpg";
}

function seedAdmin(PDO $pdo, string $email, string $password, string $fullName): void
{
    $existing = $pdo->prepare('SELECT id FROM customers WHERE email = :email');
    $existing->execute(['email' => $email]);
    if ($existing->fetch()) {
        return;
    }

    $now = gmdate('Y-m-d H:i:s');
    $stmt = $pdo->prepare(
        'INSERT INTO customers
            (id, full_name, email, password_hash, avatar_url, birth_date, is_active, role, created_at, updated_at, is_deleted)
         VALUES
            (:id, :full_name, :email, :password_hash, NULL, NULL, 1, :role, :now, NULL, 0)'
    );
    $stmt->execute([
        'id' => Uuid::v4(),
        'full_name' => $fullName,
        'email' => $email,
        'password_hash' => password_hash($password, PASSWORD_BCRYPT),
        'role' => 'Admin',
        'now' => $now,
    ]);
}

$pdo->beginTransaction();

try {
    $actionId = seedCategory($pdo, 'Ação', 'Jogos de ação e aventura.', 'acao');
    $rpgId = seedCategory($pdo, 'RPG', 'Jogos de interpretação de personagens.', 'rpg');
    $indieId = seedCategory($pdo, 'Indie', 'Jogos produzidos por estúdios independentes.', 'indie');
    $strategyId = seedCategory($pdo, 'Estratégia', 'Jogos de estratégia e gerenciamento.', 'estrategia');

    // Jogos reais com a capa oficial da Steam (o último número é o id do jogo na Steam). Preços em R$, só de exemplo.
    // Ordem dos campos: título, descrição, desenvolvedora, publicadora, preço, preço com desconto (ou null),
    // lançamento, tags, id na Steam.
    $games = [
        $actionId => [
            ['Elden Ring', 'Explore as Terras Intermédias, um mundo aberto sombrio cheio de chefes desafiadores e segredos.', 'FromSoftware', 'Bandai Namco', 229.90, 159.90, '2022-02-25', ['acao', 'souls-like', 'mundo-aberto'], 1245620],
            ['Sekiro: Shadows Die Twice', 'Um shinobi em busca de vingança no Japão feudal, com combate de espadas preciso e implacável.', 'FromSoftware', 'Activision', 199.90, null, '2019-03-22', ['acao', 'souls-like', 'ninja'], 814380],
            ['Red Dead Redemption 2', 'A história de Arthur Morgan e da gangue Van der Linde no fim da era do Velho Oeste.', 'Rockstar Games', 'Rockstar Games', 249.90, 99.90, '2019-12-05', ['acao', 'mundo-aberto', 'faroeste'], 1174180],
            ['DOOM Eternal', 'Tiro em primeira pessoa frenético: enfrente as hordas do inferno como o Doom Slayer.', 'id Software', 'Bethesda Softworks', 149.90, 37.40, '2020-03-20', ['acao', 'fps'], 782330],
            ['Devil May Cry 5', 'Combate estiloso com Nero, Dante e V contra uma invasão demoníaca.', 'Capcom', 'Capcom', 99.90, 34.90, '2019-03-08', ['acao', 'hack-and-slash'], 601150],
            ['Monster Hunter: World', 'Cace monstros gigantes sozinho ou com até três amigos e crie equipamentos com o que coletar.', 'Capcom', 'Capcom', 99.90, null, '2018-08-09', ['acao', 'cooperativo', 'cacada'], 582010],
        ],
        $rpgId => [
            ['The Witcher 3: Wild Hunt', 'Geralt de Rívia busca sua filha adotiva em um mundo aberto marcado pela guerra.', 'CD PROJEKT RED', 'CD PROJEKT RED', 129.90, 32.40, '2015-05-18', ['rpg', 'mundo-aberto', 'fantasia'], 292030],
            ["Baldur's Gate 3", 'RPG de turnos baseado em Dungeons & Dragons, com escolhas que mudam toda a história.', 'Larian Studios', 'Larian Studios', 199.90, null, '2023-08-03', ['rpg', 'turnos', 'cooperativo'], 1086940],
            ['Cyberpunk 2077', 'Viva como V, um mercenário em Night City, uma metrópole obcecada por poder e modificações corporais.', 'CD PROJEKT RED', 'CD PROJEKT RED', 199.90, 99.90, '2020-12-10', ['rpg', 'mundo-aberto', 'ficcao-cientifica'], 1091500],
            ['Disco Elysium', 'Um detetive sem memória investiga um assassinato em um RPG movido a diálogos e escolhas.', 'ZA/UM', 'ZA/UM', 89.90, null, '2019-10-15', ['rpg', 'narrativo', 'investigacao'], 632470],
            ['Persona 5 Royal', 'Estudante de dia, ladrão de corações à noite: um JRPG com estilo e batalhas por turnos.', 'ATLUS', 'SEGA', 249.90, 124.90, '2022-10-21', ['rpg', 'jrpg', 'anime'], 1687950],
            ['The Elder Scrolls V: Skyrim Special Edition', 'Um mundo aberto enorme onde você é o Dovahkiin, o caçador de dragões.', 'Bethesda Game Studios', 'Bethesda Softworks', 149.90, null, '2016-10-28', ['rpg', 'mundo-aberto', 'fantasia'], 489830],
        ],
        $indieId => [
            ['Hollow Knight', 'Explore um vasto reino arruinado de insetos neste metroidvania desenhado à mão.', 'Team Cherry', 'Team Cherry', 46.99, null, '2017-02-24', ['indie', 'metroidvania', 'dificil'], 367520],
            ['Stardew Valley', 'Herde a fazenda do seu avô, plante, pesque, faça amigos e reconstrua a cidade.', 'ConcernedApe', 'ConcernedApe', 24.99, null, '2016-02-26', ['indie', 'fazenda', 'relaxante'], 413150],
            ['Celeste', 'Ajude Madeline a escalar a montanha Celeste neste jogo de plataforma preciso e emocionante.', 'Maddy Makes Games', 'Maddy Makes Games', 36.99, 9.24, '2018-01-25', ['indie', 'plataforma', 'pixel-art'], 504230],
            ['Hades', 'Fuja do submundo como Zagreus, filho de Hades, neste roguelike de ação premiado.', 'Supergiant Games', 'Supergiant Games', 73.99, 29.59, '2020-09-17', ['indie', 'roguelike', 'mitologia'], 1145360],
            ['Dead Cells', 'Roguelite de ação em um castelo que muda a cada morte.', 'Motion Twin', 'Motion Twin', 46.99, null, '2018-08-07', ['indie', 'roguelike', 'metroidvania'], 588650],
            ['Cuphead', 'Lute contra chefes enormes em um jogo com visual de desenho animado dos anos 1930.', 'Studio MDHR', 'Studio MDHR', 36.99, null, '2017-09-29', ['indie', 'dificil', 'cooperativo'], 268910],
            ['Terraria', 'Cave, construa, explore e lute em um mundo 2D gerado aleatoriamente.', 'Re-Logic', 'Re-Logic', 19.99, 9.99, '2011-05-16', ['indie', 'sandbox', 'cooperativo'], 105600],
        ],
        $strategyId => [
            ["Sid Meier's Civilization VI", 'Guie uma civilização da Idade da Pedra até a era da informação, um turno de cada vez.', 'Firaxis Games', '2K', 129.90, 19.48, '2016-10-21', ['estrategia', 'turnos', '4x'], 289070],
            ['Age of Empires II: Definitive Edition', 'O clássico de estratégia em tempo real, remasterizado com novas campanhas.', 'Forgotten Empires', 'Xbox Game Studios', 52.99, null, '2019-11-14', ['estrategia', 'tempo-real', 'historico'], 813780],
            ['XCOM 2', 'Lidere a resistência contra a ocupação alienígena em batalhas táticas por turnos.', 'Firaxis Games', '2K', 129.90, 12.99, '2016-02-05', ['estrategia', 'turnos', 'ficcao-cientifica'], 268500],
            ['Total War: WARHAMMER III', 'Batalhas épicas e conquista de território no mundo de fantasia de Warhammer.', 'Creative Assembly', 'SEGA', 249.90, null, '2022-02-17', ['estrategia', 'tempo-real', 'fantasia'], 1142710],
            ['Frostpunk', 'Mantenha a última cidade da Terra viva em um inverno eterno, tomando decisões difíceis.', '11 bit studios', '11 bit studios', 79.99, 19.99, '2018-04-24', ['estrategia', 'construcao', 'sobrevivencia'], 323190],
            ['Into the Breach', 'Comande mechas contra monstros gigantes em batalhas táticas curtas no estilo quebra-cabeça.', 'Subset Games', 'Subset Games', 36.99, null, '2018-02-27', ['estrategia', 'turnos', 'indie'], 590380],
        ],
    ];

    foreach ($games as $categoryId => $list) {
        foreach ($list as [$title, $description, $developer, $publisher, $price, $discount, $release, $tags, $steamId]) {
            seedGame($pdo, [
                'title' => $title,
                'description' => $description,
                'developer' => $developer,
                'publisher' => $publisher,
                'price' => $price,
                'discount_price' => $discount,
                'release_date' => $release,
                'tags' => $tags,
                'cover_image_url' => steamCover($steamId),
            ], $categoryId);
        }
    }

    seedAdmin($pdo, 'admin@gamestore.com', 'Admin@123', 'Administrador');

    $pdo->commit();
    echo "Seed concluído com sucesso.\n";
    echo "Login admin -> email: admin@gamestore.com | senha: Admin@123\n";
} catch (Throwable $e) {
    $pdo->rollBack();
    fwrite(STDERR, 'Erro ao popular o banco: ' . $e->getMessage() . "\n");
    exit(1);
}
