  <!-- ==================== MODAL — Iniciar Projeto (Liquid Glass) ==================== -->
  <div class="modal-backdrop" id="project-modal" aria-hidden="true" role="dialog">
    <div class="modal-card">
      <button type="button" class="modal-close" id="modal-close-btn" onclick="closeProjectModal()" aria-label="Fechar modal">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
      </button>

      <!-- Form Body -->
      <div id="modal-form-wrapper">
        <div class="modal-header">
          <span class="modal-badge">• iniciar projeto</span>
          <h2 class="modal-title">Vamos construir algo feito junto<span class="dot-orange">.</span></h2>
          <p class="modal-desc">Preencha o formulário abaixo e receba uma análise e proposta personalizada para o seu negócio.</p>
        </div>

        <form class="modal-form" id="project-briefing-form">
          <div class="form-row">
            <div class="form-group">
              <label class="form-label" for="form-nome">Seu nome <span class="req">*</span></label>
              <input type="text" id="form-nome" name="nome" class="form-input" placeholder="Como podemos te chamar?" required>
            </div>
            <div class="form-group">
              <label class="form-label" for="form-email">Seu e-mail corporativo / pessoal <span class="req">*</span></label>
              <input type="email" id="form-email" name="email" class="form-input" placeholder="seuemail@empresa.com" required>
            </div>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label class="form-label" for="form-whatsapp">WhatsApp com DDD <span class="req">*</span></label>
              <input type="tel" id="form-whatsapp" name="whatsapp" class="form-input" placeholder="(11) 99999-9999" required>
            </div>
            <div class="form-group">
              <label class="form-label" for="form-tipo">Tipo de projeto</label>
              <select id="form-tipo" name="tipo_projeto" class="form-select">
                <option value="Site Institucional">Site Institucional</option>
                <option value="Landing Page de Alta Conversão">Landing Page de Alta Conversão</option>
                <option value="Sistema Web / Painel">Sistema Web / Painel</option>
                <option value="E-commerce">E-commerce</option>
                <option value="Redesign / Otimização">Redesign / Otimização</option>
                <option value="Outro">Outro</option>
              </select>
            </div>
          </div>

          <div class="form-group">
            <label class="form-label" for="form-orcamento">Investimento estimado / Prazo desejado</label>
            <input type="text" id="form-orcamento" name="orcamento" class="form-input" placeholder="Ex: R$ 5k a R$ 15k / Início imediato">
          </div>

          <div class="form-group">
            <label class="form-label" for="form-msg">Conte um pouco sobre o projeto ou objetivo</label>
            <textarea id="form-msg" name="mensagem" class="form-textarea" placeholder="Descreva brevemente o que você precisa alcançar..."></textarea>
          </div>

          <button type="submit" class="btn-primary form-submit-btn">
            enviar briefing & iniciar projeto
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/></svg>
          </button>
        </form>
      </div>

      <!-- Success Screen -->
      <div id="modal-success-screen" class="modal-success">
        <div class="modal-success__icon">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
        </div>
        <h3 class="modal-success__title">Briefing enviado com sucesso!</h3>
        <p class="modal-success__text">Recebemos as informações do seu projeto. Nossa equipe entrará em contato em menos de 24 horas via WhatsApp e E-mail.</p>
        
        <div class="modal-success__actions">
          <a href="https://api.whatsapp.com/send/?phone=5511946898620" target="_blank" rel="noopener" class="btn-primary" id="modal-wa-success">
            falar agora no WhatsApp
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/></svg>
          </a>
          <button type="button" class="modal-success__btn-reset" id="modal-reset-btn">enviar outro projeto</button>
        </div>
      </div>

    </div>
  </div>
