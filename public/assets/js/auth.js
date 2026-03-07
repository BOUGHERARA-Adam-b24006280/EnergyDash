document.addEventListener('DOMContentLoaded', () => {
    
    const form = document.querySelector('form');
    
    if (form) {
        form.addEventListener('submit', function() {
            const btn = document.querySelector('button[type="submit"]'); 
            
            if (btn) {
                btn.innerHTML = `
                    <span class="animate-spin inline-block size-4 border-[3px] border-current border-t-transparent text-white rounded-full" role="status" aria-label="loading"></span>
                    Chargement...
                `;
                btn.disabled = true;
                
                btn.classList.add('opacity-50', 'pointer-events-none');
            }
        });
    }
});