<?php

require '../../vendor/autoload.php'; 

use Attestto\SolanaPhpSdk\Connection;
use Attestto\SolanaPhpSdk\Keypair;
use Attestto\SolanaPhpSdk\PublicKey;
use Attestto\SolanaPhpSdk\Transaction;
use Attestto\SolanaPhpSdk\Programs\SplToken\Actions\SPLTokenActions;

$connection = new Connection('https://api.devnet.solana.com'); // Cambiar por tu RPC si es necesario
$payerKeypair = Keypair::fromSecretKey([...]); // Clave secreta del pagador
$mintPublicKey = new PublicKey('TokenMintAddressHere'); // Dirección del token mint
$destinationPublicKey = new PublicKey('DestinationAccountHere'); // Cuenta destino

// Obtener o crear una cuenta asociada para el token
$associatedTokenAccount = SPLTokenActions::getOrCreateAssociatedTokenAccount(
    $connection,
    $payerKeypair,
    $mintPublicKey,
    $destinationPublicKey
);

// Crear una transacción para transferir tokens
$transaction = new Transaction();
$transaction->add(
    SPLTokenActions::createTransferInstruction(
        $payerKeypair->getPublicKey(),
        $associatedTokenAccount->getPublicKey(),
        $destinationPublicKey,
        $amount // Cantidad de tokens a transferir
    )
);

// Enviar la transacción
$txHash = $connection->sendTransaction($transaction, [$payerKeypair]);
echo "Transacción enviada: $txHash\n";
