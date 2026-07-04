function adjugate(m) {
  return [
    m[4] * m[8] - m[5] * m[7],
    m[2] * m[7] - m[1] * m[8],
    m[1] * m[5] - m[2] * m[4],
    m[5] * m[6] - m[3] * m[8],
    m[0] * m[8] - m[2] * m[6],
    m[2] * m[3] - m[0] * m[5],
    m[3] * m[7] - m[4] * m[6],
    m[1] * m[6] - m[0] * m[7],
    m[0] * m[4] - m[1] * m[3],
  ];
}

function multiply(a, b) {
  const c = new Array(9);
  for (let i = 0; i < 3; i++) {
    for (let j = 0; j < 3; j++) {
      let s = 0;
      for (let k = 0; k < 3; k++) s += a[3 * i + k] * b[3 * k + j];
      c[3 * i + j] = s;
    }
  }
  return c;
}

function multiplyVec(m, v) {
  return [
    m[0] * v[0] + m[1] * v[1] + m[2] * v[2],
    m[3] * v[0] + m[4] * v[1] + m[5] * v[2],
    m[6] * v[0] + m[7] * v[1] + m[8] * v[2],
  ];
}

function basisToPoints(x1, y1, x2, y2, x3, y3, x4, y4) {
  const m = [x1, x2, x3, y1, y2, y3, 1, 1, 1];
  const v = multiplyVec(adjugate(m), [x4, y4, 1]);
  return multiply(m, [v[0], 0, 0, 0, v[1], 0, 0, 0, v[2]]);
}

function projection(w, h, d) {
  const s = basisToPoints(0, 0, w, 0, w, h, 0, h);
  const t = multiply(
    basisToPoints(d.tl.x, d.tl.y, d.tr.x, d.tr.y, d.br.x, d.br.y, d.bl.x, d.bl.y),
    adjugate(s)
  );
  for (let i = 0; i < 9; i++) t[i] = t[i] / t[8];
  return [t[0], t[3], 0, t[6], t[1], t[4], 0, t[7], 0, 0, 1, 0, t[2], t[5], 0, t[8]];
}

function dist(a, b) {
  return Math.hypot(b.x - a.x, b.y - a.y);
}

export function transformFor(corners, sizeW, sizeH) {
  const tl = { x: (corners.tl.x / 100) * sizeW, y: (corners.tl.y / 100) * sizeH };
  const tr = { x: (corners.tr.x / 100) * sizeW, y: (corners.tr.y / 100) * sizeH };
  const br = { x: (corners.br.x / 100) * sizeW, y: (corners.br.y / 100) * sizeH };
  const bl = { x: (corners.bl.x / 100) * sizeW, y: (corners.bl.y / 100) * sizeH };
  const gw = Math.max(1, (dist(tl, tr) + dist(bl, br)) / 2);
  const gh = Math.max(1, (dist(tl, bl) + dist(tr, br)) / 2);
  const m = projection(gw, gh, { tl, tr, br, bl });
  return { transform: `matrix3d(${m.join(",")})`, gw, gh };
}
