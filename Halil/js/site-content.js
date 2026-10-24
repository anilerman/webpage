(async function () {
  const el = (tag, className, text) => { const n = document.createElement(tag); if (className) n.className = className; if (text !== undefined) n.textContent = text; return n; };
  const safe = value => /^(https?:\/\/|assets\/|project-detail-[\w.-]+\.html$)/i.test(value || '') ? value : '';
  const image = (src, alt) => { const n = el('img'); n.src = safe(src); n.alt = alt; n.loading = 'lazy'; return n; };
  const sorted = items => [...items].sort((a,b) => (b.date || b.year || '').localeCompare(a.date || a.year || '') || (b.created_at || '').localeCompare(a.created_at || '') || (a.sort_order || 0)-(b.sort_order || 0));
  function reveal() {
    const nodes = document.querySelectorAll('.fade-up:not(.is-visible)');
    if (!('IntersectionObserver' in window)) { nodes.forEach(n => n.classList.add('is-visible')); return; }
    const observer = new IntersectionObserver(entries => entries.forEach(e => { if(e.isIntersecting) {e.target.classList.add('is-visible'); observer.unobserve(e.target);} }), {threshold: .1});
    nodes.forEach(n => observer.observe(n));
  }
  try {
    const response = await fetch('assets/content.json', {cache:'no-store'});
    if (!response.ok) throw new Error('İçerik yüklenemedi');
    const data = await response.json();
    const list = document.getElementById('projectsList');
    if (list) list.replaceChildren(...sorted(data.projects).map(p => {
      const a = el('a','project-box'); a.href = safe(p.href); const img = image(p.image_url,p.title); img.width=400; img.height=300;
      const overlay=el('div','overlay'); overlay.append(el('h3','',p.title),el('p','year',p.year)); a.append(img,overlay); return a;
    }));
    const awards=document.querySelector('.award-list');
    if (awards) awards.replaceChildren(...sorted(data.awards).map(p => {
      const li=el('li',`${p.category==='finalist'?'is-finalist':'is-award'} fade-up`);
      const text=`${p.year ? p.year+' - ' : ''}${p.title}`;
      if(safe(p.href)) {const a=el('a','',text);a.href=safe(p.href);li.append(a);} else li.textContent=text;
      return li;
    }));
    const team=document.querySelector('.team-members'), contributors=document.querySelector('.contributors-grid');
    if(team && contributors) {
      team.replaceChildren(); contributors.replaceChildren();
      sorted(data.studio).forEach(p => {
        if(p.category==='contributor') {contributors.append(el('div','contributor-item fade-up',p.title));return;}
        const card=el('div','team-member fade-up'), avatar=el('div','avatar');
        avatar.style.setProperty('--hue',Number(p.hue)||205);
        if(safe(p.image_url)) {const img=image(p.image_url,p.title);img.style.cssText='width:100%;height:100%;object-fit:cover;border-radius:50%';avatar.append(img);}
        else avatar.textContent=p.initials || p.title.split(/\s+/).map(w=>w[0]).slice(0,2).join('');
        card.append(avatar,el('p','',p.title),el('span','role',p.subtitle||''));team.append(card);
      });
    }
    const display=document.getElementById('mainDisplay');
    if(display) {
      const name=decodeURIComponent(location.pathname.split('/').pop());
      const p=data.projects.find(p=>p.href===name);
      if(p) {
        document.title=p.title+' | TET Architects'; display.src=safe(p.image_url);display.alt=p.title;
        const info=document.querySelector('.project-info');
        const meta=el('p','meta',p.meta||''), desc=el('p','desc',p.description||'');
        meta.style.whiteSpace=desc.style.whiteSpace='pre-line';info.replaceChildren(el('h2','',p.title),meta,desc);
        document.getElementById('thumbnailBar').replaceChildren(...(p.gallery||[]).map((src,i)=>{
          const thumb=image(src,`${p.title} ${i+1}`);thumb.tabIndex=0;thumb.setAttribute('role','button');
          const select=()=>{display.src=safe(src);};thumb.onclick=select;thumb.onkeydown=e=>{if(e.key==='Enter'||e.key===' '){e.preventDefault();select();}}; return thumb;
        }));
      }
    }
    reveal();
  } catch(error) { console.error(error); reveal(); }
})();
