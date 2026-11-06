/**
 * feito. — Admin Interactivity & Operations Engine
 */
document.addEventListener('DOMContentLoaded', () => {

  // --- 1. Mobile Sidebar Toggle ---
  const toggleBtn = document.getElementById('admin-sidebar-toggle');
  const sidebar = document.querySelector('.admin-sidebar');
  const overlay = document.getElementById('admin-sidebar-overlay');

  if (toggleBtn && sidebar) {
    toggleBtn.addEventListener('click', () => {
      sidebar.classList.toggle('is-open');
      if (overlay) overlay.classList.toggle('is-visible');
    });
  }

  if (overlay) {
    overlay.addEventListener('click', () => {
      if (sidebar) sidebar.classList.remove('is-open');
      overlay.classList.remove('is-visible');
    });
  }

  // --- 2. Toast Notification System ---
  window.showToast = (message, type = 'success') => {
    let container = document.getElementById('admin-toast-container');
    if (!container) {
      container = document.createElement('div');
      container.id = 'admin-toast-container';
      container.className = 'toast-container';
      document.body.appendChild(container);
    }

    const toast = document.createElement('div');
    toast.className = `toast toast--${type}`;
    
    const icon = type === 'success' 
      ? `<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#22c55e" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>`
      : `<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#ef4444" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>`;

    toast.innerHTML = icon;
    const msgSpan = document.createElement('span');
    msgSpan.textContent = String(message ?? '');
    toast.appendChild(msgSpan);
    container.appendChild(toast);

    // Fade-in animation
    setTimeout(() => toast.classList.add('is-visible'), 10);

    // Auto-remove after 4 seconds
    setTimeout(() => {
      toast.classList.remove('is-visible');
      setTimeout(() => toast.remove(), 300);
    }, 4000);
  };

  // --- 3. Slugify Helper ---
  const slugify = (str) => {
    return str
      .toLowerCase()
      .normalize('NFD')
      .replace(/[\u0300-\u036f]/g, '')
      .replace(/[^a-z0-9\s-]/g, '')
      .trim()
      .replace(/[\s-]+/g, '-');
  };

  // Auto-slug generator for Blog Editor
  const titleInput = document.getElementById('post-title');
  const slugInput = document.getElementById('post-slug');
  let slugManuallyEdited = false;

  if (titleInput && slugInput) {
    if (slugInput.value.trim() !== '') {
      slugManuallyEdited = true;
    }

    slugInput.addEventListener('input', () => {
      slugManuallyEdited = true;
    });

    titleInput.addEventListener('input', () => {
      if (!slugManuallyEdited) {
        slugInput.value = slugify(titleInput.value);
        updateSeoPreview();
      }
    });
  }

  // --- 4. Live SEO Preview ---
  const seoTitleInput = document.getElementById('seo-title');
  const seoDescInput = document.getElementById('seo-desc');
  const prevTitle = document.getElementById('preview-title');
  const prevDesc = document.getElementById('preview-desc');
  const prevUrl = document.getElementById('preview-url');

  const updateSeoPreview = () => {
    if (!prevTitle) return;
    const t = (seoTitleInput && seoTitleInput.value) || (titleInput && titleInput.value) || 'Título do Artigo';
    const d = (seoDescInput && seoDescInput.value) || 'Descrição do artigo para visualização nos resultados de busca...';
    const s = (slugInput && slugInput.value) || 'url-do-artigo';

    prevTitle.textContent = t;
    prevDesc.textContent = d;
    if (prevUrl) prevUrl.textContent = `https://feitojunto.com/blog/${s}`;
  };

  if (seoTitleInput) seoTitleInput.addEventListener('input', updateSeoPreview);
  if (seoDescInput) seoDescInput.addEventListener('input', updateSeoPreview);
  if (slugInput) slugInput.addEventListener('input', updateSeoPreview);

  // --- 5. Image Upload Preview for Blog ---
  const coverInput = document.getElementById('post-cover-file');
  const coverPreview = document.getElementById('post-cover-preview');

  if (coverInput && coverPreview) {
    coverInput.addEventListener('change', (e) => {
      const file = e.target.files[0];
      if (file) {
        const reader = new FileReader();
        reader.onload = (re) => {
          coverPreview.src = re.target.result;
          coverPreview.style.display = 'block';
        };
        reader.readAsDataURL(file);
      }
    });
  }

  // --- 6. Quick Lead Status Selector ---
  document.querySelectorAll('.js-lead-status-select').forEach(select => {
    select.addEventListener('change', async (e) => {
      const leadId = select.dataset.leadId;
      const newStatus = select.value;
      const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

      select.disabled = true;

      try {
        const res = await fetch('/admin/api/lead-status.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-Token': csrfToken
          },
          body: JSON.stringify({ lead_id: leadId, status: newStatus })
        });

        const data = await res.json();
        if (data.success) {
          window.showToast('Status do lead atualizado!');
        } else {
          window.showToast(data.message || 'Erro ao atualizar status', 'error');
        }
      } catch (err) {
        window.showToast('Falha na comunicação com o servidor', 'error');
      } finally {
        select.disabled = false;
      }
    });
  });

  // --- 7. Confirmation Modal for Destructive Actions ---
  document.querySelectorAll('[data-confirm]').forEach(el => {
    el.addEventListener('click', (e) => {
      const msg = el.getAttribute('data-confirm') || 'Tem certeza que deseja prosseguir?';
      if (!confirm(msg)) {
        e.preventDefault();
      }
    });
  });

});
