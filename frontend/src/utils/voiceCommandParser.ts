export interface ParsedVoiceCommand {
  type: 'license_plate' | 'document';
  value: string;
  confidence: number;
}

export interface VoiceCommandResult {
  success: boolean;
  data?: ParsedVoiceCommand;
  error?: string;
}

/**
 * Processa comandos de voz para extrair informações de placa ou documento
 * Exemplos de comandos suportados:
 * - "Placa ABC1234"
 * - "Documento 00515635251"
 * - "CPF 12345678900"
 * - "CNPJ 12345678000100"
 */
export const parseVoiceCommand = (transcript: string): VoiceCommandResult => {
  if (!transcript.trim()) {
    return {
      success: false,
      error: 'Nenhum comando detectado',
    };
  }

  const normalizedTranscript = transcript.toLowerCase().trim();

  // Padrões para placa
  const platePatterns = [
    /placa\s+([a-z]{3}\d{4})/i,
    /placa\s+([a-z]{3}\d{1}[a-z]\d{2})/i, // Mercosul
    /placa\s+([a-z]{3}\d{2}[a-z]\d{2})/i, // Mercosul
  ];

  // Padrões para documento
  const documentPatterns = [
    /documento\s+(\d{11})/i, // CPF
    /documento\s+(\d{14})/i, // CNPJ
    /cpf\s+(\d{11})/i,
    /cnpj\s+(\d{14})/i,
    /cpf\s+(\d{3}\.\d{3}\.\d{3}-\d{2})/i, // CPF formatado
    /cnpj\s+(\d{2}\.\d{3}\.\d{3}\/\d{4}-\d{2})/i, // CNPJ formatado
  ];

  // Tentar encontrar placa
  for (const pattern of platePatterns) {
    const match = normalizedTranscript.match(pattern);
    if (match) {
      const plate = match[1].toUpperCase();
      return {
        success: true,
        data: {
          type: 'license_plate',
          value: plate,
          confidence: 0.9,
        },
      };
    }
  }

  // Tentar encontrar documento
  for (const pattern of documentPatterns) {
    const match = normalizedTranscript.match(pattern);
    if (match) {
      let document = match[1];

      // Remover formatação se existir
      document = document.replace(/[^\d]/g, '');

      return {
        success: true,
        data: {
          type: 'document',
          value: document,
          confidence: 0.9,
        },
      };
    }
  }

  // Busca mais genérica - tentar extrair números que podem ser placa ou documento
  const numberPattern = /(\d{4,14})/g;
  const numbers = normalizedTranscript.match(numberPattern);

  if (numbers) {
    for (const number of numbers) {
      // Verificar se é uma placa (4 dígitos)
      if (number.length === 4) {
        // Verificar se há letras antes dos números
        const beforeNumbers = normalizedTranscript.substring(
          0,
          normalizedTranscript.indexOf(number)
        );
        if (
          beforeNumbers.includes('placa') ||
          beforeNumbers.includes('carro') ||
          beforeNumbers.includes('veículo')
        ) {
          return {
            success: true,
            data: {
              type: 'license_plate',
              value: number.toUpperCase(),
              confidence: 0.7,
            },
          };
        }
      }

      // Verificar se é CPF (11 dígitos)
      if (number.length === 11) {
        if (
          normalizedTranscript.includes('cpf') ||
          normalizedTranscript.includes('documento')
        ) {
          return {
            success: true,
            data: {
              type: 'document',
              value: number,
              confidence: 0.8,
            },
          };
        }
      }

      // Verificar se é CNPJ (14 dígitos)
      if (number.length === 14) {
        if (
          normalizedTranscript.includes('cnpj') ||
          normalizedTranscript.includes('documento')
        ) {
          return {
            success: true,
            data: {
              type: 'document',
              value: number,
              confidence: 0.8,
            },
          };
        }
      }
    }
  }

  return {
    success: false,
    error:
      'Comando não reconhecido. Tente dizer "Placa ABC1234" ou "Documento 12345678900"',
  };
};

/**
 * Formata uma placa para exibição
 */
export const formatLicensePlate = (plate: string): string => {
  if (!plate) return '';

  // Remove caracteres especiais
  const cleanPlate = plate.replace(/[^A-Za-z0-9]/g, '').toUpperCase();

  // Formato Mercosul: ABC1D23
  if (cleanPlate.length === 7 && /^[A-Z]{3}\d[A-Z]\d{2}$/.test(cleanPlate)) {
    return cleanPlate.replace(/([A-Z]{3})(\d)([A-Z])(\d{2})/, '$1-$2$3$4');
  }

  // Formato tradicional: ABC1234
  if (cleanPlate.length === 7 && /^[A-Z]{3}\d{4}$/.test(cleanPlate)) {
    return cleanPlate.replace(/([A-Z]{3})(\d{4})/, '$1-$2');
  }

  return cleanPlate;
};

/**
 * Formata um documento para exibição
 */
export const formatDocument = (document: string): string => {
  if (!document) return '';

  const cleanDocument = document.replace(/[^\d]/g, '');

  if (cleanDocument.length === 11) {
    // CPF
    return cleanDocument.replace(/(\d{3})(\d{3})(\d{3})(\d{2})/, '$1.$2.$3-$4');
  } else if (cleanDocument.length === 14) {
    // CNPJ
    return cleanDocument.replace(
      /(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/,
      '$1.$2.$3/$4-$5'
    );
  }

  return cleanDocument;
};
