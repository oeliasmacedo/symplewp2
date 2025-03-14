<?php

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config.php';

use WPManager\Services\PostService;
use WPManager\Services\PageService;
use WPManager\Services\PluginService;
use WPManager\Services\ThemeService;

// Configuração de headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: ' . FRONTEND_URL);
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Tratamento de requisição OPTIONS (CORS)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Função para validar o token da API
function validateApiToken() {
    $headers = getallheaders();
    $token = isset($headers['Authorization']) ? str_replace('Bearer ', '', $headers['Authorization']) : null;
    
    if (!$token || $token !== API_TOKEN) {
        http_response_code(401);
        echo json_encode(['error' => 'Token inválido ou não fornecido']);
        exit;
    }
}

// Função para enviar resposta JSON
function sendJsonResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    echo json_encode($data);
    exit;
}

// Função para obter o corpo da requisição
function getRequestBody() {
    return json_decode(file_get_contents('php://input'), true);
}

try {
    // Validação do token para todas as requisições exceto OPTIONS
    if ($_SERVER['REQUEST_METHOD'] !== 'OPTIONS') {
        validateApiToken();
    }

    // Parse da URL para determinar o endpoint
    $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $uri = explode('/', trim($uri, '/'));
    $endpoint = isset($uri[2]) ? $uri[2] : '';
    $id = isset($uri[3]) ? $uri[3] : null;

    // Instancia os serviços necessários
    $postService = new PostService();
    $pageService = new PageService();
    $pluginService = new PluginService();

    switch ($endpoint) {
        case 'stats':
            if ($_SERVER['REQUEST_METHOD'] === 'GET') {
                $stats = [
                    'posts' => $postService->count(),
                    'pages' => $pageService->count(),
                    'plugins' => $pluginService->count()
                ];
                sendJsonResponse($stats);
            }
            break;

        case 'posts':
            switch ($_SERVER['REQUEST_METHOD']) {
                case 'GET':
                    if ($id) {
                        $post = $postService->get($id);
                        sendJsonResponse($post);
                    } else {
                        $posts = $postService->list();
                        sendJsonResponse($posts);
                    }
                    break;

                case 'POST':
                    $data = getRequestBody();
                    $postId = $postService->create($data);
                    sendJsonResponse(['id' => $postId], 201);
                    break;

                case 'PUT':
                    if (!$id) {
                        sendJsonResponse(['error' => 'ID não fornecido'], 400);
                    }
                    $data = getRequestBody();
                    $postService->update($id, $data);
                    sendJsonResponse(['success' => true]);
                    break;

                case 'DELETE':
                    if (!$id) {
                        sendJsonResponse(['error' => 'ID não fornecido'], 400);
                    }
                    $postService->delete($id);
                    sendJsonResponse(['success' => true]);
                    break;
            }
            break;

        case 'pages':
            switch ($_SERVER['REQUEST_METHOD']) {
                case 'GET':
                    if ($id) {
                        $page = $pageService->get($id);
                        sendJsonResponse($page);
                    } else {
                        $pages = $pageService->list();
                        sendJsonResponse($pages);
                    }
                    break;

                case 'POST':
                    $data = getRequestBody();
                    $pageId = $pageService->create($data);
                    sendJsonResponse(['id' => $pageId], 201);
                    break;

                case 'PUT':
                    if (!$id) {
                        sendJsonResponse(['error' => 'ID não fornecido'], 400);
                    }
                    $data = getRequestBody();
                    $pageService->update($id, $data);
                    sendJsonResponse(['success' => true]);
                    break;

                case 'DELETE':
                    if (!$id) {
                        sendJsonResponse(['error' => 'ID não fornecido'], 400);
                    }
                    $pageService->delete($id);
                    sendJsonResponse(['success' => true]);
                    break;
            }
            break;

        case 'plugins':
            switch ($_SERVER['REQUEST_METHOD']) {
                case 'GET':
                    if ($id) {
                        $plugin = $pluginService->get($id);
                        sendJsonResponse($plugin);
                    } else {
                        $plugins = $pluginService->list();
                        sendJsonResponse($plugins);
                    }
                    break;

                case 'POST':
                    $data = getRequestBody();
                    $pluginId = $pluginService->install($data['plugin_slug']);
                    sendJsonResponse(['id' => $pluginId], 201);
                    break;

                case 'PUT':
                    if (!$id) {
                        sendJsonResponse(['error' => 'ID não fornecido'], 400);
                    }
                    $pluginService->update($id);
                    sendJsonResponse(['success' => true]);
                    break;

                case 'DELETE':
                    if (!$id) {
                        sendJsonResponse(['error' => 'ID não fornecido'], 400);
                    }
                    $pluginService->uninstall($id);
                    sendJsonResponse(['success' => true]);
                    break;
            }
            break;

        default:
            sendJsonResponse(['error' => 'Endpoint não encontrado'], 404);
    }
} catch (Exception $e) {
    sendJsonResponse([
        'error' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ], 500);
} 