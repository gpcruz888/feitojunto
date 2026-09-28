<?php
/**
 * Mailer Service — feito.
 * Disparo resiliente de e-mails para novos leads e confirmações.
 */
require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/Env.php';
require_once __DIR__ . '/Logger.php';

class Mailer {
    /**
     * Envia notificação para a equipe feito. quando um lead é cadastrado
     */
    public static function notifyNewLead(array $lead): bool {
        $db = Database::getInstance();

        // Obtém destinatários das configurações ou .env
        $stmt = $db->prepare("SELECT value FROM settings WHERE key = 'notification_email'");
        $stmt->execute();
        $toConfig = $stmt->fetchColumn();

        $to = !empty($toConfig) ? $toConfig : Env::get('ADMIN_NOTIFICATION_EMAIL', 'gabriel@feitojunto.com, contato@feitojunto.com');
        $fromEmail = Env::get('MAIL_FROM_EMAIL', 'noreply@feitojunto.com');
        $fromName = Env::get('MAIL_FROM_NAME', 'feito. Notificações');

        $nome = htmlspecialchars($lead['nome'] ?? 'Não informado');
        $email = htmlspecialchars($lead['email'] ?? 'Não informado');
        $whatsapp = htmlspecialchars($lead['whatsapp'] ?? 'Não informado');
        $tipo = htmlspecialchars($lead['tipo_projeto'] ?? 'Geral');
        $orcamento = htmlspecialchars($lead['prazo_orcamento'] ?? 'Não informado');
        $mensagem = nl2br(htmlspecialchars($lead['mensagem'] ?? 'Sem mensagem'));
        $data = date('d/m/Y \à\s H:i');
        $leadId = $lead['id'] ?? 0;
        $appUrl = rtrim(Env::get('APP_URL', 'https://feitojunto.com'), '/');
        $leadUrl = "{$appUrl}/admin/leads?id={$leadId}";

        $subject = "=?UTF-8?B?" . base64_encode("Novo Lead no Site: {$nome} — feito.") . "?=";

        $body = "
        <!DOCTYPE html>
        <html>
        <head>
          <meta charset='utf-8'>
          <title>Novo Lead — feito.</title>
          <style>
            body { margin: 0; padding: 32px 16px; background-color: #090909; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; color: #ffffff; }
            .card { max-width: 600px; margin: 0 auto; background-color: #141414; border: 1px solid #282830; border-radius: 16px; padding: 36px; box-shadow: 0 10px 30px rgba(0,0,0,0.5); }
            .badge { display: inline-block; padding: 4px 10px; border-radius: 999px; background-color: rgba(255, 90, 0, 0.15); color: #FF5A00; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 12px; }
            h2 { color: #ffffff; margin: 0 0 24px 0; font-size: 22px; font-weight: 700; }
            h2 span { color: #FF5A00; }
            .grid { margin-bottom: 24px; border-top: 1px solid #222228; }
            .row { padding: 12px 0; border-bottom: 1px solid #222228; }
            .label { font-size: 11px; color: #888899; text-transform: uppercase; font-weight: 600; letter-spacing: 0.04em; }
            .val { font-size: 15px; color: #ffffff; margin-top: 4px; }
            .val a { color: #FF5A00; text-decoration: none; }
            .msg-box { background: rgba(255, 255, 255, 0.03); border: 1px solid #222228; border-radius: 10px; padding: 14px; margin-top: 6px; font-size: 14px; line-height: 1.5; color: #e4e4e7; }
            .actions { margin-top: 28px; text-align: left; }
            .btn { display: inline-block; background-color: #ffffff; color: #090909; padding: 12px 24px; border-radius: 999px; text-decoration: none; font-weight: 700; font-size: 13px; margin-right: 12px; }
            .btn-wa { display: inline-block; background-color: #25d366; color: #ffffff; padding: 12px 24px; border-radius: 999px; text-decoration: none; font-weight: 700; font-size: 13px; }
            .footer { margin-top: 32px; font-size: 12px; color: #666677; text-align: center; }
          </style>
        </head>
        <body>
          <div class='card'>
            <div class='badge'>• novo lead recebido</div>
            <h2>projeto recebido pelo site<span>.</span></h2>
            
            <div class='grid'>
              <div class='row'>
                <div class='label'>Nome do Contato:</div>
                <div class='val'>{$nome}</div>
              </div>
              <div class='row'>
                <div class='label'>E-mail:</div>
                <div class='val'><a href='mailto:{$email}'>{$email}</a></div>
              </div>
              <div class='row'>
                <div class='label'>WhatsApp:</div>
                <div class='val'><a href='https://wa.me/" . preg_replace('/\D/', '', $whatsapp) . "' target='_blank'>{$whatsapp}</a></div>
              </div>
              <div class='row'>
                <div class='label'>Tipo de Projeto:</div>
                <div class='val'>{$tipo}</div>
              </div>
              <div class='row'>
                <div class='label'>Investimento / Prazo:</div>
                <div class='val'>{$orcamento}</div>
              </div>
              <div class='row'>
                <div class='label'>Mensagem / Detalhes:</div>
                <div class='msg-box'>{$mensagem}</div>
              </div>
              <div class='row'>
                <div class='label'>Data & Hora:</div>
                <div class='val'>{$data}</div>
              </div>
            </div>

            <div class='actions'>
              <a href='{$leadUrl}' class='btn'>Abrir no Painel Admin</a>
              <a href='https://wa.me/" . preg_replace('/\D/', '', $whatsapp) . "' class='btn-wa'>Chamar no WhatsApp</a>
            </div>

            <div class='footer'>
              feito. — presença digital feita do jeito certo.<br>
              Este é um e-mail automático gerado pelo sistema.
            </div>
          </div>
        </body>
        </html>
        ";

        $headers = [
            'MIME-Version: 1.0',
            'Content-type: text/html; charset=UTF-8',
            "From: {$fromName} <{$fromEmail}>",
            "Reply-To: {$nome} <{$email}>",
            'X-Mailer: PHP/' . phpversion()
        ];

        $sent = @mail($to, $subject, $body, implode("\r\n", $headers));

        // Atualiza status do e-mail no lead
        if ($leadId > 0) {
            $upStmt = $db->prepare("
                UPDATE leads 
                SET mail_notified = ?, mail_error = ? 
                WHERE id = ?
            ");
            $upStmt->execute([
                $sent ? 1 : 0,
                $sent ? null : 'Falha no envio via mail() nativo do servidor',
                $leadId
            ]);
        }

        return $sent;
    }

    /**
     * Envia e-mail de confirmação automática para o próprio lead (se ativado nas configurações)
     */
    public static function sendAutoReply(array $lead): bool {
        $db = Database::getInstance();

        $stmt = $db->prepare("SELECT key, value FROM settings WHERE key IN ('auto_reply_enabled', 'auto_reply_subject', 'auto_reply_message')");
        $stmt->execute();
        $configs = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

        if (($configs['auto_reply_enabled'] ?? '0') !== '1') {
            return false;
        }

        $to = $lead['email'] ?? '';
        if (empty($to) || !filter_var($to, FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        $fromEmail = Env::get('MAIL_FROM_EMAIL', 'noreply@feitojunto.com');
        $fromName = Env::get('MAIL_FROM_NAME', 'feito.');
        $subjectText = $configs['auto_reply_subject'] ?? 'Recebemos seu projeto — feito.';
        $subject = "=?UTF-8?B?" . base64_encode($subjectText) . "?=";
        $messageBody = nl2br(htmlspecialchars($configs['auto_reply_message'] ?? 'Recebemos sua mensagem com sucesso.'));

        $body = "
        <!DOCTYPE html>
        <html>
        <body style='background-color:#090909; color:#ffffff; font-family:sans-serif; padding:24px;'>
          <div style='max-width:550px; margin:0 auto; background-color:#141414; border:1px solid #282830; border-radius:16px; padding:32px;'>
            <h2 style='color:#ffffff; margin-top:0;'>feito<span style='color:#FF5A00;'>.</span></h2>
            <div style='font-size:15px; line-height:1.6; color:#e0e0e0;'>{$messageBody}</div>
            <div style='margin-top:24px; padding-top:16px; border-top:1px solid #282830; font-size:12px; color:#888899;'>
              feito. CNPJ 65.988.531/0001-80 — sua presença digital, feita do jeito certo.
            </div>
          </div>
        </body>
        </html>
        ";

        $headers = [
            'MIME-Version: 1.0',
            'Content-type: text/html; charset=UTF-8',
            "From: {$fromName} <{$fromEmail}>",
            'X-Mailer: PHP/' . phpversion()
        ];

        return @mail($to, $subject, $body, implode("\r\n", $headers));
    }
}
