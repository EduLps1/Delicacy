<?php
/**
 * =============================================
 * DELICACY - Serviço de Email
 * =============================================
 * 
 * Serviço básico de envio de emails para o MVP.
 * Utiliza a função mail() nativa do PHP.
 * 
 * Raciocínio da escolha do mail() nativo:
 * - Simples e sem dependências externas (adequado para MVP)
 * - Funciona em qualquer hospedagem com sendmail configurado
 * - Em produção, substituir por PHPMailer ou biblioteca SMTP
 *   para maior confiabilidade e funcionalidades (HTML, anexos, etc.)
 * 
 * Limitações do mail() nativo:
 * - Depende de configuração do servidor (sendmail/postfix)
 * - Sem suporte a autenticação SMTP
 * - Emails podem cair no spam com mais frequência
 * - Sem confirmação real de entrega
 * 
 * Uso:
 *   $emailService = new EmailService();
 *   $emailService->sendConfirmationEmail('dono@restaurante.com', 'Restaurante X');
 */

namespace Delicacy\Services;

class EmailService
{
    /**
     * @var string Endereço de email do remetente
     */
    private $fromEmail;

    /**
     * @var string Nome do remetente
     */
    private $fromName;

    /**
     * Construtor — carrega configurações de email das constantes.
     */
    public function __construct()
    {
        $this->fromEmail = defined('MAIL_FROM') ? MAIL_FROM : 'noreply@delicacy.com.br';
        $this->fromName  = defined('MAIL_FROM_NAME') ? MAIL_FROM_NAME : 'Delicacy';
    }

    /**
     * Envia email de confirmação de cadastro para o restaurante.
     * Enviado após o registro bem-sucedido de um novo restaurante.
     * 
     * @param string $toEmail         Email do destinatário
     * @param string $restaurantName  Nome do restaurante cadastrado
     * @return bool True se o email foi aceito para envio (não garante entrega)
     */
    public function sendConfirmationEmail(string $toEmail, string $restaurantName): bool
    {
        $subject = "Bem-vindo ao Delicacy - Cadastro Confirmado!";

        // Template do email em texto plano (MVP)
        // Em produção, usar template HTML com branding
        $body = $this->buildConfirmationBody($restaurantName);

        return $this->send($toEmail, $subject, $body);
    }

    /**
     * Envia email de notificação de suspensão de conta.
     * Enviado quando o Admin Delicacy suspende um restaurante.
     * 
     * @param string $toEmail         Email do destinatário
     * @param string $restaurantName  Nome do restaurante
     * @param string $reason          Motivo da suspensão
     * @return bool True se o email foi aceito para envio
     */
    public function sendSuspensionEmail(string $toEmail, string $restaurantName, string $reason = ''): bool
    {
        $subject = "Delicacy - Conta Suspensa";

        $body  = "Olá,\r\n\r\n";
        $body .= "Informamos que a conta do restaurante '{$restaurantName}' foi suspensa na plataforma Delicacy.\r\n\r\n";
        if (!empty($reason)) {
            $body .= "Motivo: {$reason}\r\n\r\n";
        }
        $body .= "Para mais informações, entre em contato com nosso suporte.\r\n\r\n";
        $body .= "Atenciosamente,\r\n";
        $body .= "Equipe Delicacy";

        return $this->send($toEmail, $subject, $body);
    }

    /**
     * Monta o corpo do email de confirmação de cadastro.
     * 
     * @param string $restaurantName Nome do restaurante
     * @return string Corpo do email formatado
     */
    private function buildConfirmationBody(string $restaurantName): string
    {
        $body  = "Olá!\r\n\r\n";
        $body .= "Seja bem-vindo(a) à plataforma Delicacy!\r\n\r\n";
        $body .= "O cadastro do restaurante '{$restaurantName}' foi realizado com sucesso.\r\n\r\n";
        $body .= "Agora você pode:\r\n";
        $body .= "- Acessar seu painel administrativo\r\n";
        $body .= "- Criar seus cardápios digitais\r\n";
        $body .= "- Gerenciar seus pedidos\r\n\r\n";
        $body .= "Acesse seu painel em: " . (defined('BASE_URL') ? BASE_URL : 'http://localhost:8000') . "/empresa/login\r\n\r\n";
        $body .= "Atenciosamente,\r\n";
        $body .= "Equipe Delicacy\r\n";

        return $body;
    }

    /**
     * Método interno de envio de email.
     * Encapsula a função mail() do PHP com headers de segurança.
     * 
     * Headers configurados:
     * - From: Identifica o remetente
     * - Reply-To: Para onde respostas devem ir
     * - MIME-Version e Content-Type: Formatação correta do email
     * - X-Mailer: Identificação do sistema que enviou
     * 
     * @param string $to      Email do destinatário
     * @param string $subject Assunto do email
     * @param string $body    Corpo do email
     * @return bool True se mail() retornou true
     */
    private function send(string $to, string $subject, string $body): bool
    {
        // Monta os headers do email
        $headers = [
            "From: {$this->fromName} <{$this->fromEmail}>",
            "Reply-To: {$this->fromEmail}",
            "MIME-Version: 1.0",
            "Content-Type: text/plain; charset=UTF-8",
            "X-Mailer: Delicacy-Platform/1.0"
        ];

        $headerString = implode("\r\n", $headers);

        // Tenta enviar o email
        try {
            $sent = @mail($to, $subject, $body, $headerString);

            if ($sent) {
                error_log("DELICACY EMAIL: Email enviado para {$to} - Assunto: {$subject}");
            } else {
                error_log("DELICACY EMAIL: Falha ao enviar email para {$to} - Assunto: {$subject}");
            }

            return $sent;
        } catch (\Exception $e) {
            error_log("DELICACY EMAIL: Exceção ao enviar email: " . $e->getMessage());
            return false;
        }
    }
}
