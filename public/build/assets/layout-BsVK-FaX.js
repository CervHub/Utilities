import{r as x,j as e,$ as m}from"./app-DgFN-T0p.js";import{a as o,B as f}from"./app-logo-icon-DFO7IgS1.js";import{P as h}from"./index-DZXRyeE0.js";var p="Separator",i="horizontal",u=["horizontal","vertical"],l=x.forwardRef((a,t)=>{const{decorative:r,orientation:s=i,...c}=a,n=j(s)?s:i,d=r?{role:"none"}:{"aria-orientation":n==="vertical"?n:void 0,role:"separator"};return e.jsx(h.div,{"data-orientation":n,...d,...c,ref:t})});l.displayName=p;function j(a){return u.includes(a)}var N=l;function g({className:a,orientation:t="horizontal",decorative:r=!0,...s}){return e.jsx(N,{"data-slot":"separator-root",decorative:r,orientation:t,className:o("bg-border shrink-0 data-[orientation=horizontal]:h-px data-[orientation=horizontal]:w-full data-[orientation=vertical]:h-full data-[orientation=vertical]:w-px",a),...s})}function O({title:a,description:t}){return e.jsxs("header",{children:[e.jsx("h3",{className:"mb-0.5 text-base font-medium",children:a}),t&&e.jsx("p",{className:"text-muted-foreground text-sm",children:t})]})}function v({title:a,description:t}){return e.jsxs("div",{className:"mb-8 space-y-0.5",children:[e.jsx("h2",{className:"text-xl font-semibold tracking-tight",children:a}),t&&e.jsx("p",{className:"text-muted-foreground text-sm",children:t})]})}const w=[{title:"Profile",href:"/settings/profile",icon:null},{title:"Password",href:"/settings/password",icon:null},{title:"Appearance",href:"/settings/appearance",icon:null}];function z({children:a}){if(typeof window>"u")return null;const t=window.location.pathname;return e.jsxs("div",{className:"px-4 py-6",children:[e.jsx(v,{title:"Settings",description:"Manage your profile and account settings"}),e.jsxs("div",{className:"flex flex-col space-y-8 lg:flex-row lg:space-y-0 lg:space-x-12",children:[e.jsx("aside",{className:"w-full max-w-xl lg:w-48",children:e.jsx("nav",{className:"flex flex-col space-y-1 space-x-0",children:w.map((r,s)=>e.jsx(f,{size:"sm",variant:"ghost",asChild:!0,className:o("w-full justify-start",{"bg-muted":t===r.href}),children:e.jsx(m,{href:r.href,prefetch:!0,children:r.title})},`${r.href}-${s}`))})}),e.jsx(g,{className:"my-6 md:hidden"}),e.jsx("div",{className:"flex-1 md:max-w-2xl",children:e.jsx("section",{className:"max-w-xl space-y-12",children:a})})]})]})}export{O as H,z as S};
