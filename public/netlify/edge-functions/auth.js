const USER = Deno.env.get("SITE_USER") || "Splmn1testsite";
const PASS = Deno.env.get("SITE_PASS") || "Boss649205739LLL!!!";
const REALM = 'Basic realm="Bonus Park", charset="UTF-8"';

function same(a, b) {
  if (a.length !== b.length) return false;
  let diff = 0;
  for (let i = 0; i < a.length; i += 1) diff |= a.charCodeAt(i) ^ b.charCodeAt(i);
  return diff === 0;
}

export default async (request, context) => {
  const header = request.headers.get("authorization") || "";
  if (header.startsWith("Basic ")) {
    try {
      const decoded = atob(header.slice(6));
      const split = decoded.indexOf(":");
      if (split > 0) {
        const user = decoded.slice(0, split);
        const pass = decoded.slice(split + 1);
        if (same(user, USER) && same(pass, PASS)) return context.next();
      }
    } catch {
      /* битый заголовок — просим заново */
    }
  }
  return new Response("401 Unauthorized", {
    status: 401,
    headers: {
      "WWW-Authenticate": REALM,
      "Cache-Control": "no-store",
      "Content-Type": "text/plain; charset=utf-8",
    },
  });
};

export const config = { path: "/*" };
