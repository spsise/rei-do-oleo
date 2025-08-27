<?php

namespace App\Contracts;

/**
 * Interface para métodos de tracking de mensagens
 * Esta interface define os métodos que devem estar disponíveis
 * para qualquer classe que precise rastrear execução de métodos
 */
interface MessageTrackingInterface
{
    /**
     * Inicia o tracking de um método
     */
    public function trackMethod(string $methodName, array $inputData = [], array $outputData = []): void;

    /**
     * Finaliza o tracking de um método
     */
    public function endMethod(string $methodName, array $outputData = []): void;

    /**
     * Inicializa o sistema de tracking
     */
    public function initializeTracking(): void;
}
