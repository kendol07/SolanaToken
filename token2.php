<?php

require '../../vendor/autoload.php';

use Attestto\SolanaPhpSdk\Connection;
use Attestto\SolanaPhpSdk\Keypair;
use Attestto\SolanaPhpSdk\PublicKey;
use Attestto\SolanaPhpSdk\Programs\SplToken\Actions\SPLTokenActions;
use Attestto\SolanaPhpSdk\Transaction;

class TokenSender {
    use SPLTokenActions;

    public function sendTokens(
        string $rpcEndpoint,
        array $payerSecretKey,
        string $mintAddress,
        string $destinationAddress,
        int $amount
    ) {
        // Crear conexión al nodo RPC
        $connection = new Connection($rpcEndpoint);

        // Crear Keypair del pagador
        $payer = Keypair::fromSecretKey($payerSecretKey);

        // Convertir las direcciones a objetos PublicKey
        $mint = new PublicKey($mintAddress);
        $destination = new PublicKey($destinationAddress);

        // Obtener el blockhash reciente
        $recentBlockhash = $connection->getLatestBlockhash()['blockhash'];

        // Crear o recuperar la cuenta asociada para el token
        $associatedTokenAccount = $this->getOrCreateAssociatedTokenAccount(
            $connection,
            $payer,
            $mint,
            $destination
        );

        // Crear instrucción para transferir tokens
        $transaction = new Transaction($recentBlockhash);
        $transferInstruction = $this->createTransferInstruction(
            $payer->getPublicKey(),
            $associatedTokenAccount->getPublicKey(),
            $destination,
            $amount
        );
        $transaction->add($transferInstruction);

        // Asignar el pagador de las tarifas
        $transaction->feePayer = $payer->getPublicKey();

        // Firmar y enviar la transacción
        $txHash = $connection->sendTransaction($transaction, [$payer]);

        return $txHash;
    }
}

// Ejemplo de uso
$sender = new TokenSender();
$rpcEndpoint = 'https://api.devnet.solana.com'; // Cambiar según la red
$payerSecretKey = [/* Clave secreta del pagador */];
$mintAddress = 'TokenMintAddressHere'; // Dirección del token mint
$destinationAddress = 'DestinationAccountHere'; // Dirección del destinatario
$amount = 1000000; // Cantidad en la menor unidad del token

try {
    $txHash = $sender->sendTokens($rpcEndpoint, $payerSecretKey, $mintAddress, $destinationAddress, $amount);
    echo "Transacción enviada con éxito: $txHash\n";
} catch (Exception $e) {
    echo "Error al enviar la transacción: " . $e->getMessage() . "\n";
}
