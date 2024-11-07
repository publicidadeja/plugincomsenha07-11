<?php
// teste-licenca.php

require_once('../../../wp-load.php'); // Carrega o WordPress

// Teste de criação de licenças
echo "<h2>Teste do Sistema de Licenças</h2>";

echo "<h3>Criando Licença de Teste</h3>";
$codigo_teste = gma_criar_licenca_teste();
echo "Licença de teste criada: " . $codigo_teste . "<br><br>";

echo "<h3>Criando Licença PRO</h3>";
$codigo_pro = gma_criar_licenca_pro();
echo "Licença PRO criada: " . $codigo_pro . "<br><br>";

echo "<h3>Ativando Licença PRO</h3>";
if (gma_ativar_licenca($codigo_pro)) {
    echo "Licença ativada com sucesso!<br><br>";
} else {
    echo "Erro ao ativar licença<br><br>";
}

echo "<h3>Verificando Licença</h3>";
if (gma_verificar_licenca()) {
    echo "Licença válida<br>";
} else {
    echo "Licença inválida ou expirada<br>";
}