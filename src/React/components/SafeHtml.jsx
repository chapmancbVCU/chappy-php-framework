import React, { useMemo } from 'react';
import DOMPurify from 'dompurify';

function decodeEntities(str = '') {
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
  decode = true,
  ...rest
}) {
  const sanitized = useMemo(() => {
    if (!html) return '';

    // Create an ISOLATED DOMPurify instance for this call instead of mutating
    // the global singleton. Hooks added here cannot leak into — or be removed
    // by — another SafeHtml rendering in the same tick.
    const purify = DOMPurify();  // factory call returns a fresh instance

    // Link hardening (open in new tab, neutralize dangerous hrefs).
    if (addTargetBlank) {
      purify.addHook('afterSanitizeAttributes', (node) => {
        if (node.nodeName === 'A') {
          const href = node.getAttribute('href') || '';
          if (/^\s*(javascript:|data:)/i.test(href)) node.setAttribute('href', '#');
          node.setAttribute('target', '_blank');
          const rel = (node.getAttribute('rel') || '').split(/\s+/).filter(Boolean);
          if (!rel.includes('noopener')) rel.push('noopener');
          if (!rel.includes('noreferrer')) rel.push('noreferrer');
          node.setAttribute('rel', rel.join(' '));
        }
      });
    }

    // Iframe host allow-listing + sandboxing.
    if (allowIframes && allowedIframeHosts.length > 0) {
      purify.addHook('uponSanitizeElement', (node) => {
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
      });
    }

    let src = String(html);
    if (decode) src = decodeEntities(src);

    const config = {
      USE_PROFILES: { html: true },
      FORBID_TAGS: ['script', 'style', 'object', 'embed', 'link', ...(allowIframes ? [] : ['iframe'])],
      ...(stripStyle ? { FORBID_ATTR: ['style'] } : {}),
    };
    if (allowedTags)  config.ALLOWED_TAGS = allowedTags;
    if (allowedAttrs) config.ALLOWED_ATTR = allowedAttrs;

    // No manual hook cleanup needed — the instance is discarded after this call.
    return purify.sanitize(src, config);
  }, [
    html, decode, allowIframes, addTargetBlank, stripStyle,
    JSON.stringify(allowedIframeHosts),
    JSON.stringify(allowedTags),
    JSON.stringify(allowedAttrs),
  ]);

  return <div className={className} dangerouslySetInnerHTML={{ __html: sanitized }} {...rest} />;
}