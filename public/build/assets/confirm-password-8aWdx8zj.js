import{m as d,j as s,L as m}from"./app-DgFN-T0p.js";import{L as l,I as c}from"./label-De7Ydqrb.js";import{B as u}from"./app-logo-icon-DFO7IgS1.js";import{I as f}from"./input-DvKc0J5m.js";import{A as w}from"./auth-layout-BubPGYoA.js";import{L as h}from"./loader-circle-DNSIA1aG.js";import"./index-DZXRyeE0.js";function y(){const{data:o,setData:e,post:t,processing:a,errors:i,reset:n}=d({password:""}),p=r=>{r.preventDefault(),t(route("password.confirm"),{onFinish:()=>n("password")})};return s.jsxs(w,{title:"Confirm your password",description:"This is a secure area of the application. Please confirm your password before continuing.",children:[s.jsx(m,{title:"Confirm password"}),s.jsx("form",{onSubmit:p,children:s.jsxs("div",{className:"space-y-6",children:[s.jsxs("div",{className:"grid gap-2",children:[s.jsx(l,{htmlFor:"password",children:"Password"}),s.jsx(f,{id:"password",type:"password",name:"password",placeholder:"Password",autoComplete:"current-password",value:o.password,autoFocus:!0,onChange:r=>e("password",r.target.value)}),s.jsx(c,{message:i.password})]}),s.jsx("div",{className:"flex items-center",children:s.jsxs(u,{className:"w-full",disabled:a,children:[a&&s.jsx(h,{className:"h-4 w-4 animate-spin"}),"Confirm password"]})})]})})]})}export{y as default};
