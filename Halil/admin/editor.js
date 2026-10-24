(() => {
  const field=document.getElementById('gallery');
  const url=document.getElementById('image_url'), file=document.getElementById('image_file');
  if(url && file) {
    const preview=document.createElement('img'); preview.alt='Kapak önizlemesi';preview.style.cssText='max-width:180px;max-height:130px;object-fit:cover;border-radius:8px;display:none;margin:12px 0';file.after(preview);
    const show=src=>{preview.src=src;preview.style.display=src?'block':'none';};
    url.addEventListener('input',()=>show(/^(https?:\/\/)/.test(url.value)?url.value:'../'+url.value));
    file.addEventListener('change',()=>{if(file.files[0])show(URL.createObjectURL(file.files[0]));});
    if(url.value)show(/^(https?:\/\/)/.test(url.value)?url.value:'../'+url.value);
  }
  if(!field)return;
  const list=document.createElement('div');list.style.cssText='max-height:420px;overflow:auto;margin-bottom:16px';field.after(list);
  function render() {
    const paths=field.value.split(/\r?\n/).filter(Boolean);list.replaceChildren();
    paths.forEach((path,index)=>{
      const row=document.createElement('div');row.style.cssText='display:flex;align-items:center;gap:8px;margin:8px 0';
      const img=document.createElement('img');img.src=/^https?:\/\//.test(path)?path:'../'+path;img.alt=`Galeri ${index+1}`;img.loading='lazy';img.style.cssText='width:70px;height:48px;object-fit:cover';row.append(img);
      const label=document.createElement('span');label.textContent=String(index+1);row.append(label);
      [['↑',-1],['↓',1],['Kaldır',0]].forEach(([label,step])=>{
        const button=document.createElement('button');button.type='button';button.className='btn secondary';button.textContent=label;button.disabled=step!==0 && (index+step<0 || index+step>=paths.length);
        button.onclick=()=>{if(step===0)paths.splice(index,1);else [paths[index],paths[index+step]]=[paths[index+step],paths[index]];field.value=paths.join('\n');render();};row.append(button);
      });list.append(row);
    });
  }
  field.addEventListener('input',render);render();
})();
