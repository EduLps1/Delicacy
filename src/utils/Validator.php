<?php
/**
 * =============================================
 * DELICACY - Classe de Validação de Dados
 * =============================================
 * 
 * Centraliza TODAS as validações de entrada do sistema.
 * Acumula erros em um array para feedback completo ao usuário.
 * 
 * Raciocínio: Uma classe centralizada evita duplicação de lógica
 * de validação nos controllers. Todos os controllers usam a mesma
 * implementação, garantindo consistência.
 * 
 * Uso:
 *   $validator = new Validator();
 *   $validator->validateRequired($name, 'nome');
 *   $validator->validateEmail($email);
 *   $validator->validateCNPJ($cnpj);
 *   if ($validator->hasErrors()) {
 *       $errors = $validator->getErrors();
 *   }
 */

namespace Delicacy\Utils;

class Validator
{
    /**
     * @var array Lista de erros de validação acumulados
     * Cada entrada é uma string descritiva do erro encontrado.
     */
    private $errors = [];

    // =============================================
    // VALIDAÇÕES DE CAMPO GENÉRICAS
    // =============================================

    /**
     * Valida se um campo obrigatório foi preenchido.
     * Rejeita strings vazias, null e strings só com espaços.
     * 
     * @param mixed  $value     Valor do campo
     * @param string $fieldName Nome do campo (para mensagem de erro amigável)
     * @return bool True se válido
     */
    public function validateRequired($value, string $fieldName): bool
    {
        if (empty($value) || (is_string($value) && trim($value) === '')) {
            $this->errors[] = "O campo '{$fieldName}' é obrigatório.";
            return false;
        }
        return true;
    }

    /**
     * Valida comprimento mínimo de uma string.
     * 
     * @param string $value     Valor do campo
     * @param int    $min       Tamanho mínimo exigido
     * @param string $fieldName Nome do campo
     * @return bool True se válido
     */
    public function validateMinLength(string $value, int $min, string $fieldName): bool
    {
        if (mb_strlen(trim($value)) < $min) {
            $this->errors[] = "O campo '{$fieldName}' deve ter no mínimo {$min} caracteres.";
            return false;
        }
        return true;
    }

    /**
     * Valida comprimento máximo de uma string.
     * 
     * @param string $value     Valor do campo
     * @param int    $max       Tamanho máximo permitido
     * @param string $fieldName Nome do campo
     * @return bool True se válido
     */
    public function validateMaxLength(string $value, int $max, string $fieldName): bool
    {
        if (mb_strlen(trim($value)) > $max) {
            $this->errors[] = "O campo '{$fieldName}' deve ter no máximo {$max} caracteres.";
            return false;
        }
        return true;
    }

    // =============================================
    // VALIDAÇÃO DE EMAIL
    // =============================================

    /**
     * Valida formato de email usando filter_var do PHP.
     * 
     * Raciocínio: filter_var(FILTER_VALIDATE_EMAIL) implementa a RFC 5322
     * e é mais confiável que regex customizados para validação de email.
     * 
     * @param string $email Email a validar
     * @return bool True se válido
     */
    public function validateEmail(string $email): bool
    {
        $email = trim($email);

        if (empty($email)) {
            $this->errors[] = "O campo 'email' é obrigatório.";
            return false;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->errors[] = "O email informado não é válido.";
            return false;
        }

        return true;
    }

    // =============================================
    // VALIDAÇÃO DE CNPJ
    // =============================================

    /**
     * Valida CNPJ com verificação de dígitos verificadores.
     * 
     * Algoritmo de validação do CNPJ:
     * 1. Remove formatação (pontos, barra, hífen)
     * 2. Verifica se tem 14 dígitos
     * 3. Rejeita CNPJs com todos os dígitos iguais (ex: 11111111111111)
     * 4. Calcula o 1º dígito verificador (posição 13) usando pesos 5,4,3,2,9,8,7,6,5,4,3,2
     * 5. Calcula o 2º dígito verificador (posição 14) usando pesos 6,5,4,3,2,9,8,7,6,5,4,3,2
     * 6. Compara os dígitos calculados com os informados
     * 
     * @param string $cnpj CNPJ com ou sem formatação
     * @return bool True se válido
     */
    public function validateCNPJ(string $cnpj): bool
    {
        // Remove caracteres de formatação (., /, -)
        $cnpj = preg_replace('/[^0-9]/', '', $cnpj);

        // Deve ter exatamente 14 dígitos
        if (strlen($cnpj) !== 14) {
            $this->errors[] = "O CNPJ deve conter 14 dígitos.";
            return false;
        }

        // Rejeita CNPJs com todos os dígitos iguais
        // Raciocínio: 00000000000000, 11111111111111, etc. passariam na
        // verificação matemática mas não são CNPJs válidos
        if (preg_match('/^(\d)\1{13}$/', $cnpj)) {
            $this->errors[] = "O CNPJ informado não é válido.";
            return false;
        }

        // Cálculo do 1º dígito verificador
        $weights1 = [5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];
        $sum = 0;
        for ($i = 0; $i < 12; $i++) {
            $sum += (int)$cnpj[$i] * $weights1[$i];
        }
        $remainder = $sum % 11;
        $digit1 = ($remainder < 2) ? 0 : 11 - $remainder;

        if ((int)$cnpj[12] !== $digit1) {
            $this->errors[] = "O CNPJ informado não é válido.";
            return false;
        }

        // Cálculo do 2º dígito verificador
        $weights2 = [6, 5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];
        $sum = 0;
        for ($i = 0; $i < 13; $i++) {
            $sum += (int)$cnpj[$i] * $weights2[$i];
        }
        $remainder = $sum % 11;
        $digit2 = ($remainder < 2) ? 0 : 11 - $remainder;

        if ((int)$cnpj[13] !== $digit2) {
            $this->errors[] = "O CNPJ informado não é válido.";
            return false;
        }

        return true;
    }

    // =============================================
    // VALIDAÇÃO DE TELEFONE
    // =============================================

    /**
     * Valida telefone brasileiro.
     * Aceita formatos: (XX)XXXXX-XXXX, XXXXXXXXXXX, etc.
     * Deve ter 10 ou 11 dígitos (fixo ou celular com DDD).
     * 
     * @param string $phone Telefone a validar
     * @return bool True se válido
     */
    public function validatePhone(string $phone): bool
    {
        // Remove tudo que não é dígito
        $phone = preg_replace('/[^0-9]/', '', $phone);

        // Telefone brasileiro: 10 dígitos (fixo) ou 11 dígitos (celular)
        if (strlen($phone) < 10 || strlen($phone) > 11) {
            $this->errors[] = "O telefone deve ter 10 ou 11 dígitos (com DDD).";
            return false;
        }

        return true;
    }

    // =============================================
    // VALIDAÇÃO DE SENHA
    // =============================================

    /**
     * Valida força da senha.
     * Requisitos mínimos:
     * - Comprimento mínimo definido em MIN_PASSWORD_LENGTH (padrão 8)
     * - Pelo menos uma letra maiúscula
     * - Pelo menos uma letra minúscula
     * - Pelo menos um número
     * 
     * Raciocínio: Senhas fracas são o vetor de ataque mais comum.
     * Estes requisitos mínimos previnem senhas triviais como "123456".
     * 
     * @param string $password Senha a validar
     * @return bool True se válido
     */
    public function validatePassword(string $password): bool
    {
        $minLength = defined('MIN_PASSWORD_LENGTH') ? MIN_PASSWORD_LENGTH : 8;
        $isValid = true;

        if (strlen($password) < $minLength) {
            $this->errors[] = "A senha deve ter no mínimo {$minLength} caracteres.";
            $isValid = false;
        }

        if (!preg_match('/[A-Z]/', $password)) {
            $this->errors[] = "A senha deve conter pelo menos uma letra maiúscula.";
            $isValid = false;
        }

        if (!preg_match('/[a-z]/', $password)) {
            $this->errors[] = "A senha deve conter pelo menos uma letra minúscula.";
            $isValid = false;
        }

        if (!preg_match('/[0-9]/', $password)) {
            $this->errors[] = "A senha deve conter pelo menos um número.";
            $isValid = false;
        }

        return $isValid;
    }

    // =============================================
    // SANITIZAÇÃO
    // =============================================

    /**
     * Sanitiza uma string de entrada.
     * Remove espaços extras, converte caracteres especiais HTML.
     * 
     * Raciocínio sobre htmlspecialchars():
     * Converte <, >, &, " e ' para entidades HTML, prevenindo XSS.
     * Se um atacante inserir <script>alert('xss')</script>, será
     * convertido para texto inofensivo na exibição.
     * 
     * ENT_QUOTES: Converte tanto aspas duplas quanto simples.
     * UTF-8: Garante que caracteres multibyte sejam tratados corretamente.
     * 
     * @param string $input String a sanitizar
     * @return string String sanitizada
     */
    public static function sanitize(string $input): string
    {
        return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }

    /**
     * Sanitiza um CNPJ removendo todos os caracteres não numéricos.
     * Retorna apenas os 14 dígitos.
     * 
     * @param string $cnpj CNPJ com ou sem formatação
     * @return string CNPJ somente com dígitos
     */
    public static function sanitizeCNPJ(string $cnpj): string
    {
        return preg_replace('/[^0-9]/', '', $cnpj);
    }

    /**
     * Sanitiza um telefone removendo todos os caracteres não numéricos.
     * 
     * @param string $phone Telefone com ou sem formatação
     * @return string Telefone somente com dígitos
     */
    public static function sanitizePhone(string $phone): string
    {
        return preg_replace('/[^0-9]/', '', $phone);
    }

    // =============================================
    // GERENCIAMENTO DE ERROS
    // =============================================

    /**
     * Verifica se há erros de validação acumulados.
     * 
     * @return bool True se há pelo menos um erro
     */
    public function hasErrors(): bool
    {
        return !empty($this->errors);
    }

    /**
     * Retorna todos os erros de validação acumulados.
     * 
     * @return array Lista de mensagens de erro
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Retorna o primeiro erro de validação (útil para exibição simples).
     * 
     * @return string|null Primeira mensagem de erro ou null
     */
    public function getFirstError(): ?string
    {
        return $this->errors[0] ?? null;
    }

    /**
     * Limpa todos os erros de validação acumulados.
     * Útil para reutilizar a mesma instância do Validator.
     */
    public function clearErrors(): void
    {
        $this->errors = [];
    }

    /**
     * Adiciona um erro customizado manualmente.
     * Útil para validações de negócio que não se encaixam nos métodos genéricos.
     * 
     * @param string $message Mensagem de erro
     */
    public function addError(string $message): void
    {
        $this->errors[] = $message;
    }
}
