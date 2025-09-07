import React, { useMemo } from 'react';
import DOMPurify from 'dompurify';

function decodeEntities(str = '') {
  // Quick exit if it doesn't look encoded
  if (!/&(?:lt|gt|amp|quot|#39);/i.test(str)) return str;
  const doc = new DOMParser().parseFromString(String(str), 'text/html');
  return doc.documentElement.textContent || '';
}

export default function SafeHtml({
  html,
  className,
  allowIframes = false,
  allowedIframeHosts = [],
  addTargetBlank = true,
  stripStyle = false,
  allowedTags,
  allowedAttrs,
  decode = true,           // 👈 NEW: enable entity decoding by default
  ...rest
}) {
  const sanitized = useMemo(() => {
    if (!html) return '';

    // ---- Hooks (scoped to this call) ----
    const hooks = [];

    const stripEventAttrs = (node) => {
      if (!node?.attributes) return;
      [...node.attributes].forEach((attr) => {
        if (/^on/i.test(attr.name)) node.removeAttribute(attr.name);
      });
    };
    DOMPurify.addHook('afterSanitizeAttributes', stripEventAttrs);
    hooks.push(['afterSanitizeAttributes', stripEventAttrs]);

    if (addTargetBlank) {
      const hardenLinks = (node) => {
        if (node.nodeName === 'A') {
          const href = node.getAttribute('href') || '';
          if (/^\s*(javascript:|data:)/i.test(href)) node.setAttribute('href', '#');
          node.setAttribute('target', '_blank');
          const rel = (node.getAttribute('rel') || '').split(/\s+/).filter(Boolean);
          if (!rel.includes('noopener')) rel.push('noopener');
          if (!rel.includes('noreferrer')) rel.push('noreferrer');
          node.setAttribute('rel', rel.join(' '));
        }
      };
      DOMPurify.addHook('afterSanitizeAttributes', hardenLinks);
      hooks.push(['afterSanitizeAttributes', hardenLinks]);
    }

    if (allowIframes && allowedIframeHosts.length > 0) {
      const gateIframes = (node) => {
        if (node.nodeName !== 'IFRAME') return;
        const src = node.getAttribute('src') || '';
        try {
          const u = new URL(src, window.location.origin);
          const ok = allowedIframeHosts.some(h => u.hostname === h || u.hostname.endsWith(`.${h}`));
          if (!ok) return node.parentNode?.removeChild(node);
          if (!node.hasAttribute('sandbox')) {
            node.setAttribute('sandbox', 'allow-scripts allow-same-origin allow-presentation');
          }
        } catch {
          node.parentNode?.removeChild(node);
        }
      };
      DOMPurify.addHook('uponSanitizeElement', gateIframes);
      hooks.push(['uponSanitizeElement', gateIframes]);
    }

    // 👇 Decode entities (turn &lt;p&gt; into <p>) *before* sanitizing
    let src = String(html);
    if (decode) src = decodeEntities(src);

    const config = {
      USE_PROFILES: { html: true },
      FORBID_TAGS: ['script', 'style', 'object', 'embed', 'link', ...(allowIframes ? [] : ['iframe'])],
      ...(stripStyle ? { FORBID_ATTR: ['style'] } : {}),
    };
    if (allowedTags)  config.ALLOWED_TAGS = allowedTags;
    if (allowedAttrs) config.ALLOWED_ATTR = allowedAttrs;

    const out = DOMPurify.sanitize(src, config);

    // Cleanup hooks
    hooks.forEach(([name, fn]) => { try { DOMPurify.removeHook(name, fn); } catch {} });

    return out;
  }, [
    html, decode, allowIframes, addTargetBlank, stripStyle,
    JSON.stringify(allowedIframeHosts),
    JSON.stringify(allowedTags),
    JSON.stringify(allowedAttrs),
  ]);

  return <div className={className} dangerouslySetInnerHTML={{ __html: sanitized }} {...rest} />;
}
