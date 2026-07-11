<?php

$router->get('/login', ['AuthController', 'showLogin']);
$router->post('/login', ['AuthController', 'login']);
$router->get('/logout', ['AuthController', 'logout']);
$router->get('/register', ['AuthController', 'showRegister']);
$router->post('/register', ['AuthController', 'register']);

$router->get('/admin/dashboard', ['AdminController', 'dashboard']);
$router->get('/admin/users/create', ['AdminController', 'createUser']);
$router->post('/admin/users', ['AdminController', 'storeUser']);
$router->post('/admin/users/update-role', ['AdminController', 'updateUserRole']);
$router->post('/admin/users/{id}/delete', ['AdminController', 'deleteUser']);

$router->get('/categories', ['CategoryController', 'index']);
$router->get('/categories/create', ['CategoryController', 'create']);
$router->post('/categories', ['CategoryController', 'store']);
$router->get('/categories/{id}/edit', ['CategoryController', 'edit']);
$router->post('/categories/{id}/update', ['CategoryController', 'update']);
$router->post('/categories/{id}/delete', ['CategoryController', 'destroy']);

$router->get('/tickets', ['TicketController', 'index']);
$router->get('/tickets/create', ['TicketController', 'create']);
$router->post('/tickets', ['TicketController', 'store']);
$router->get('/tickets/{id}', ['TicketController', 'show']);
$router->get('/tickets/{id}/edit', ['TicketController', 'edit']);
$router->post('/tickets/{id}/update', ['TicketController', 'update']);
$router->post('/tickets/{id}/delete', ['TicketController', 'destroy']);
$router->post('/tickets/{id}/status', ['TicketController', 'updateStatus']);
$router->post('/tickets/{id}/assign', ['TicketController', 'assign']);
$router->post('/tickets/{id}/comment', ['TicketController', 'comment']);

$router->post('/comments/{id}/update', ['TicketController', 'updateComment']);
$router->post('/comments/{id}/delete', ['TicketController', 'deleteComment']);
$router->post('/attachments/{id}/delete', ['TicketController', 'deleteAttachment']);

$router->get('/reports/export-excel', ['ReportController', 'exportExcel']);
$router->get('/tickets/{id}/export-pdf', ['ReportController', 'exportPdf']);
