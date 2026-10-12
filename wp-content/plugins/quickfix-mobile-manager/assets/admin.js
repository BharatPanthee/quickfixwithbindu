(() => {
  'use strict';
  const root = document.getElementById('quickfix-manager');
  if (!root) return;
  const resource = root.dataset.resource, cfg = window.QuickFixAdmin;
  const feedback = document.getElementById('quickfix-feedback');
  let page = 1, filters = {}, currentItems = [], activeForm;
  const uuid = () => crypto.randomUUID?.() || [...crypto.getRandomValues(new Uint8Array(16))].map(x=>x.toString(16).padStart(2,'0')).join('');
  const labels = { 'home-placements': 'Featured placements', users: 'App users', campaigns: 'Notifications', audit: 'Activity log' };
  const el = (tag, text, attrs = {}) => {
    const node = document.createElement(tag);
    if (text != null) node.textContent = text;
    Object.entries(attrs).forEach(([k,v]) => k === 'class' ? node.className = v : node.setAttribute(k,v));
    return node;
  };
  const button = (text, action, primary = false) => {
    const node = el('button', text, { type: 'button', class: `button ${primary ? 'button-primary' : ''}` });
    node.addEventListener('click', () => Promise.resolve(action()).catch(error)); return node;
  };
  const error = (e) => { feedback.replaceChildren(el('div', e.message || 'Request failed. Please retry.', { class: 'notice notice-error', role: 'alert' })); feedback.scrollIntoView({block:'start',behavior:'smooth'}); };
  const success = (text) => { feedback.replaceChildren(el('div', text, { class: 'notice notice-success' })); };
  async function api(path = '', method = 'GET', body) {
    const response = await fetch(cfg.api + path, { method, credentials: 'same-origin', headers: { 'X-WP-Nonce': cfg.nonce, 'Content-Type': 'application/json', 'Idempotency-Key': uuid() }, ...(body ? { body: JSON.stringify(body) } : {}) });
    const json = await response.json();
    if (!response.ok) throw new Error(json.error?.message || json.message || 'Request failed.');
    return json.data;
  }
  const human = (v) => String(v ?? '').replaceAll('_', ' ');
  const localTime = (value) => {
    if (!value) return '';
    const date = new Date(value.includes('T') ? value : value.replace(' ', 'T') + 'Z');
    const parts = Object.fromEntries(new Intl.DateTimeFormat('en-CA', { timeZone: cfg.timezone, year:'numeric', month:'2-digit', day:'2-digit', hour:'2-digit', minute:'2-digit', hourCycle:'h23' }).formatToParts(date).map(x => [x.type,x.value]));
    return `${parts.year}-${parts.month}-${parts.day}T${parts.hour}:${parts.minute}`;
  };
  const utcTime = (value) => {
    if (!value) return null;
    const expected = Date.parse(value + ':00Z'); let guess = expected;
    for (let i=0;i<3;i++) { const rendered = localTime(new Date(guess).toISOString()); const delta = expected - Date.parse(rendered + ':00Z'); if (!delta) return new Date(guess).toISOString().replace('.000Z','Z'); guess += delta; }
    throw new Error('This local time does not exist because of a daylight-saving transition. Choose another time.');
  };
  function field(form, name, label, value = '', type = 'text', choices) {
    const wrapper = el('label', label); let control;
    if (choices) { control = el('select'); choices.forEach(choice => { const pair = Array.isArray(choice) ? choice : [choice, human(choice)]; control.append(el('option', pair[1], { value: pair[0] })); }); }
    else if (type === 'textarea') control = el('textarea');
    else control = el('input', null, { type });
    control.name = name;
    if (type === 'checkbox') control.checked = !!Number(value);
    else control.value = type === 'datetime-local' ? localTime(value) : (value ?? '');
    wrapper.append(control); form.append(wrapper); return control;
  }
  function form(title) {
    activeForm?.remove(); const node = el('form', null, { class:'qf-form', 'aria-label': title }); node.append(el('h2',title));
    node.addEventListener('submit', e => e.preventDefault()); root.append(node); activeForm=node; node.scrollIntoView({block:'start',behavior:'smooth'}); return node;
  }
  function values(node) { const data={}; new FormData(node).forEach((v,k) => data[k]=v); node.querySelectorAll('input[type=checkbox]').forEach(c => { if(c.name && !c.disabled)data[c.name]=c.checked; }); return data; }
  const json = (data) => el('pre', JSON.stringify(data,null,2), { class:'qf-json' });
  function confirmation(title, names, action) {
    const node = form(title); node.classList.add('qf-confirm'); node.append(el('p', `${names.length} item(s) will be affected:`)); const list=el('ul'); names.forEach(name=>list.append(el('li',name)));node.append(list);
    const confirm=field(node,'confirm','I reviewed the items and confirm this action.',0,'checkbox');
    const go=button('Confirm',async()=>{if(!confirm.checked)throw new Error('Select the confirmation checkbox.');go.disabled=true;try{const result=await action();node.remove();await load();if(result?.warning)error(new Error(`${result.message} ${result.warning}`));else success(result?.message||'Action completed.');}catch(e){go.disabled=false;throw e;}},true);
    node.append(go,button('Cancel',()=>node.remove())); confirm.focus();
  }
  function preview(node, data) {
    if (!data) return;
    const box=el('section',null,{class:'qf-preview','aria-label':'App preview'});box.append(el('h3',data.title));
    if(data.thumbnail)box.append(el('img',null,{src:data.thumbnail,alt:data.title,loading:'lazy'}));box.append(el('p',data.summary));box.append(el('p',data.total_minutes?`${data.total_minutes} minutes`:'Time not provided'));box.append(json(data));node.append(box);
  }
  async function editRecipe(item) {
    const latest=await api(`recipes/${item.id}`),meta=latest.metadata;const node=form(`Curate: ${latest.title}`);
    node.append(el('p',meta.state==='excluded'?'First select Draft and save. Then reopen this recipe, select Published, and save again. These settings affect the app only.':'Choose Published and save to make an eligible recipe available in the app. These settings affect the app only.'));
    const transitions={excluded:['excluded','draft'],draft:['draft','excluded','published','scheduled'],scheduled:['scheduled','draft','published','withdrawn'],published:['published','withdrawn'],withdrawn:['withdrawn','draft','published','scheduled']};
    field(node,'state','App state',meta.state,'text',transitions[meta.state]);
    field(node,'available_from',`Available from (${cfg.timezone})`,meta.available_from,'datetime-local');field(node,'available_to',`Available until (${cfg.timezone})`,meta.available_to,'datetime-local');
    field(node,'summary_override','App summary override',meta.summary_override,'textarea');field(node,'image_override','App thumbnail override (approved HTTPS host)',meta.image_override,'url');
    field(node,'dietary_tags','Explicitly curated dietary tags (comma-separated; do not infer allergy safety)',meta.dietary_tags.join(', '));
    const categories=await api('categories?limit=100'); const options=el('fieldset');options.append(el('legend','Categories'));categories.items.forEach(category=>{const label=el('label',category.name);const check=el('input',null,{type:'checkbox',value:category.id});check.dataset.category=category.id;check.checked=meta.categories.includes(String(category.id));label.append(check);options.append(label);});node.append(options);
    field(node,'comments_enabled','Allow new comments',meta.comments_enabled,'checkbox');field(node,'ratings_enabled','Allow ratings',meta.ratings_enabled,'checkbox');
    if(latest.validation.length)node.append(el('p',latest.validation.join(' '),{class:'qf-warning'}));preview(node,latest.preview);
    const save=button('Save curation',async()=>{const data=values(node);data.categories=[...node.querySelectorAll('[data-category]:checked')].map(x=>x.value);data.dietary_tags=data.dietary_tags.split(',').map(x=>x.trim()).filter(Boolean);data.available_from=utcTime(data.available_from);data.available_to=utcTime(data.available_to);data.revision=Number(meta.revision);save.disabled=true;try{const saved=await api(`recipes/${item.id}`,'PATCH',data);await load();if(saved.metadata.state==='draft'){await editRecipe(item);success('Draft saved. Select Published and save to publish this recipe.');}else success(saved.metadata.state==='published'?'Recipe published to the app.':'Curation saved.');}catch(e){save.disabled=false;throw e;}},true);
    node.append(save,button('Close',()=>node.remove()));node.querySelector('select').focus();
  }
  async function editResource(item) {
    const create=!item; const latest=item?await api(`${resource}/${item.id}`):{};const node=form(`${create?'Add':'Edit'} ${labels[resource]||resource}`);
    if(resource==='categories') {field(node,'name','Name',latest.name);field(node,'slug','Unique slug',latest.slug);field(node,'description','Description',latest.description,'textarea');field(node,'image_ref','Image URL',latest.image_ref,'url');field(node,'sort_order','Display order',latest.sort_order||0,'number');field(node,'enabled','Enabled',latest.enabled??1,'checkbox');}
    if(resource==='home-placements') {field(node,'recipe_id','App-published recipe ID',latest.recipe_id);field(node,'position','Position',latest.position||0,'number');field(node,'starts_at',`Starts (${cfg.timezone})`,latest.starts_at,'datetime-local');field(node,'ends_at',`Ends (${cfg.timezone})`,latest.ends_at,'datetime-local');}
    if(resource==='campaigns') {field(node,'title','Title (maximum 80 characters)',latest.title);field(node,'body','Body (maximum 180 characters)',latest.body,'textarea');field(node,'target','Destination: home or app-published recipe ID',latest.target||'home');field(node,'audience','Audience',latest.audience||'all','text',['all','test']);field(node,'scheduled_at',`Scheduled send (${cfg.timezone}); empty for send now`,latest.scheduled_at,'datetime-local');const box=el('section',null,{class:'qf-preview'});const heading=el('h3','Android / iOS preview'),copy=el('p');box.append(heading,copy);const refresh=()=>copy.textContent=`${node.elements.title.value}\n${node.elements.body.value}`;node.addEventListener('input',refresh);refresh();node.append(box,el('p','Saving creates a draft. Sending or scheduling requires a separate confirmation.'));
    }
    const states={comments:['pending','approved','rejected','hidden','deleted'],ratings:['active','held','hidden','deleted'],reports:['open','investigating','resolved','dismissed'],users:['active','community_suspended']};
    if(states[resource]) {node.append(json(latest));field(node,'status','Status',latest.status,'text',states[resource]);field(node,'reason','Reason',latest.moderation_reason||latest.resolution||'','textarea');if(resource==='comments'||resource==='reports')field(node,'assigned_to','Assigned WordPress moderator ID (optional)',latest.assigned_to||'','number');}
    if(resource==='users')node.append(button('Request Firebase password recovery',()=>confirmation('Send password recovery',[latest.display_name],()=>api(`users/${latest.id}/password-reset`,'POST',{confirm:true}))));
    const save=button('Save',async()=>{const data=values(node);delete data.brand_reply;data.revision=Number(latest.revision||0);['sort_order','position','assigned_to'].forEach(key=>{if(key in data){if(data[key]==='')delete data[key];else data[key]=Number(data[key]);}});['starts_at','ends_at','scheduled_at'].forEach(key=>{if(key in data)data[key]=utcTime(data[key]);});save.disabled=true;try{await api(create?resource:`${resource}/${item.id}`,create?'POST':'PATCH',data);await load();success('Saved.');}catch(e){save.disabled=false;throw e;}},true);node.append(save,button('Close',()=>node.remove()));
    if(resource==='comments'&&latest.status==='approved'&&latest.author_type==='consumer') {const reply=field(node,'brand_reply','Brand reply (separate attributed comment)','','textarea');node.append(button('Post brand reply',async()=>{await api(`comments/${item.id}/reply`,'POST',{revision:Number(latest.revision),reply:reply.value});await load();success('Brand reply published.');}));}
    if(resource==='campaigns'&&latest.id&&latest.status==='draft')node.append(button('Review and confirm send',()=>confirmation('Send notification campaign',[`${latest.title} — ${latest.audience} opted-in devices — ${latest.scheduled_at||'send now'}`],()=>api(`campaigns/${latest.id}/send`,'POST',{revision:Number(latest.revision),confirm:true}))));
    if(['categories','home-placements','campaigns'].includes(resource)&&latest.id)node.append(button(resource==='campaigns'?'Cancel campaign':'Remove',()=>confirmation('Confirm removal',[latest.name||latest.title||`Recipe ${latest.recipe_id}`],()=>api(`${resource}/${latest.id}`,'DELETE',{revision:Number(latest.revision),confirm:true}))));
    node.querySelector('input,select,textarea')?.focus();
  }
  async function settings() {
    const data=await api('settings');const node=form('App settings');node.append(el('p','Server credentials must be configured outside the public web root. These fields contain public configuration only.'));
    Object.entries(data).forEach(([name,value])=>{if(name==='revision')return;if(name==='firebase_client')field(node,name,'Public Firebase web client configuration (JSON)',JSON.stringify(value,null,2),'textarea');else if(name==='home_sections')field(node,name,'Home section order (comma-separated featured, categories, latest)',value.join(', '));else field(node,name,human(name),typeof value==='boolean'?Number(value):value,typeof value==='boolean'?'checkbox':typeof value==='number'?'number':name==='about'||name==='maintenance_message'?'textarea':'text');});
    node.append(button('Save settings',async()=>{const payload=values(node);payload.firebase_client=JSON.parse(payload.firebase_client);payload.home_sections=payload.home_sections.split(',').map(x=>x.trim()).filter(Boolean);Object.entries(data).forEach(([key,value])=>{if(typeof value==='number')payload[key]=Number(payload[key]);});payload.revision=data.revision;await api('settings','PATCH',payload);await load();success('Settings saved.');},true));
  }
  async function dashboard() {
    const data=await api('dashboard');const grid=el('div',null,{class:'qf-grid'});Object.entries(data.counts).forEach(([key,count])=>{const stat=el('div',null,{class:'qf-stat'});stat.append(el('strong',count),el('span',human(key)));grid.append(stat);});root.append(grid);
    const actions=el('div',null,{class:'qf-actions'});[['Select recipes','recipes'],['Pending comments','comments'],['Compose notification','campaigns']].forEach(([text,target])=>{const a=el('a',text,{href:`admin.php?page=quickfix-${target}`,class:'button'});actions.append(a);});root.append(actions,el('h2','Operational diagnostics'),json(data.diagnostics));if(data.latest_campaign)root.append(el('h2','Latest campaign'),json(data.latest_campaign));
    if(!data.diagnostics.firebase_configured||!data.diagnostics.external_scheduler_confirmed)root.append(el('p','Identity services or the external scheduler need configuration before launch. Public browsing can be tested independently.',{class:'qf-warning'}));
    if(cfg.capabilities.quickfix_manage_settings)root.append(button('Run one bounded queue pass',async()=>{await api('jobs/run','POST',{});await load();success('Queue pass finished.');}));
  }
  function toolbar() {
    const node=el('form',null,{class:'qf-toolbar'});node.addEventListener('submit',e=>{e.preventDefault();page=1;filters=values(node);Object.keys(filters).forEach(key=>{if(!filters[key])delete filters[key];});load().catch(error);});field(node,'q','Search',filters.q||'');
    if(resource==='recipes'){field(node,'state','App state',filters.state||'','text',[['','All states'],'excluded','draft','scheduled','published','withdrawn']);field(node,'source_status','Source status',filters.source_status||'','text',[['','All source states'],'publish','draft','private','trash','missing']);field(node,'category','Category ID',filters.category||'','number');field(node,'featured','Featured only',filters.featured||0,'checkbox');field(node,'missing','Missing required fields',filters.missing||0,'checkbox');}
    else if(['comments','ratings','reports','users','campaigns'].includes(resource))field(node,'status','Status',filters.status||'');
    const submit=el('button','Search / filter',{type:'submit',class:'button'});node.append(submit,button('Clear filters',()=>{filters={};page=1;return load();}));root.append(node);
  }
  function bulk() {
    const node=el('div',null,{class:'qf-actions'}),select=el('select',null,{'aria-label':'Bulk recipe action'});['draft','publish','withdraw','assign_category','clear_featured'].forEach(value=>select.append(el('option',human(value),{value})));node.append(select);
    const category=el('input',null,{type:'number',placeholder:'Category ID','aria-label':'Category ID for bulk assignment'});node.append(category,button('Review bulk action',()=>{const selected=[...root.querySelectorAll('[data-select]:checked')].map(x=>currentItems.find(item=>item.id===x.dataset.select));if(!selected.length)throw new Error('Select at least one recipe.');const action=select.value;confirmation(`Bulk ${human(action)}${action==='publish'?' — eligible excluded recipes will enter Draft before publication':''}`,selected.map(x=>`${x.title} (#${x.id})`),async()=>{const result=await api('recipes/bulk','POST',{items:selected.map(x=>({id:x.id,revision:Number(x.metadata.revision)})),action,...(action==='assign_category'?{category:category.value}:{}),confirm:true});const failed=result.results.filter(x=>!x.ok);return{message:`${result.results.length-failed.length} of ${result.results.length} recipes updated.`,warning:failed.map(x=>`#${x.id}: ${x.error.message}`).join(' ')};});}));root.append(node);
  }
  async function load() {
    root.setAttribute('aria-busy','true');root.replaceChildren();activeForm=null;
    try {
      if(resource==='dashboard'){await dashboard();return;}
      if(resource==='settings'){await settings();return;}
      toolbar();if(['categories','home-placements','campaigns'].includes(resource))root.append(button('Add new',()=>editResource(null),true));if(resource==='recipes')bulk();
      if(resource==='home-placements'){const layout=await api('home-layout');const box=el('form',null,{class:'qf-form'});box.addEventListener('submit',e=>e.preventDefault());const order=field(box,'home_sections','Enabled section order (featured, categories, latest)',layout.home_sections.join(', '));box.append(button('Save home sections',async()=>{await api('home-layout','PATCH',{revision:layout.revision,home_sections:order.value.split(',').map(x=>x.trim()).filter(Boolean)});await load();success('Home layout saved.');},true));root.append(box);}
      const params=new URLSearchParams({...filters,page,limit:25});const data=await api(`${resource}?${params}`);currentItems=data.items;
      if(!data.items.length){root.append(el('p','No records match these filters. New source recipes are excluded until explicitly curated.'));return;}
      const columns={recipes:['title','source_status','state','categories','featured','revision'],categories:['id','name','slug','enabled','sort_order'], 'home-placements':['id','recipe_id','position','starts_at','ends_at'],comments:['id','recipe_id','user_id','body','status','source_channel','created_at'],ratings:['id','recipe_id','user_id','stars','status','created_at'],reports:['id','target_type','target_id','reason','status','assigned_to'],users:['id','firebase_uid','display_name','email','verified','status','last_seen'],campaigns:['id','title','target','audience','status','accepted','permanent_failures'],audit:['id','action','target','actor_type','created_at','metadata']}[resource];
      const table=el('table',null,{class:'wp-list-table widefat striped qf-table'});table.append(el('caption',`${labels[resource]||human(resource)} — page ${page}, ${data.total} total`));const head=el('thead'),headrow=el('tr');if(resource==='recipes')headrow.append(el('th','Select',{scope:'col'}));columns.forEach(column=>headrow.append(el('th',human(column),{scope:'col'})));if(resource!=='audit')headrow.append(el('th','Actions',{scope:'col'}));head.append(headrow);table.append(head);const tbody=el('tbody');
      data.items.forEach(item=>{const row=el('tr');if(resource==='recipes'){const cell=el('td'),check=el('input',null,{type:'checkbox','aria-label':`Select ${item.title}`});check.dataset.select=item.id;cell.append(check);row.append(cell);}columns.forEach(column=>{let value=item[column];if(resource==='recipes'){if(column==='state'||column==='revision')value=item.metadata[column];if(column==='categories')value=item.preview?.categories.map(x=>x.name).join(', ')||'—';if(column==='featured')value=item.featured?'Yes':'—';}const cell=el('td',human(value));if(column==='title'&&item.preview?.thumbnail)cell.prepend(el('img',null,{src:item.preview.thumbnail,alt:'',loading:'lazy'}));row.append(cell);});if(resource!=='audit'){const cell=el('td');cell.append(button(resource==='recipes'?'Curate / preview':'Review / edit',()=>resource==='recipes'?editRecipe(item):editResource(item)));row.append(cell);}tbody.append(row);});table.append(tbody);const wrap=el('div',null,{class:'qf-table-wrap'});wrap.append(table);root.append(wrap);
      const navigation=el('nav',null,{'aria-label':'Table pagination',class:'qf-actions'});if(page>1)navigation.append(button('Previous',()=>{page--;return load();}));if(page*25<data.total)navigation.append(button('Next',()=>{page++;return load();}));root.append(navigation);
    } catch(e){error(e);}finally{root.setAttribute('aria-busy','false');}
  }
  load();
})();
