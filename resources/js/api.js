/**
 * Helpers para desembrulhar respostas da API padronizada { message, data }.
 *
 * Contrato:
 * - Endpoints simples: ApiResponse::success($payload) → unwrap(res) retorna $payload
 * - Endpoints paginados: ApiResponse::success($paginator) → unwrapPage(res) retorna { data, current_page, last_page, total, ... }
 */

/**
 * Extrai o payload de uma resposta simples (objeto ou array).
 * Aceita resposta Axios (res) ou JSON bruto de fetch (json).
 *
 * @example
 * const book = unwrap(await axios.get('/api/books/1'));
 * const json = await fetch('/api/books').then(r => r.json());
 * const data = unwrap(json);
 */
export function unwrap(resOrJson) {
  const body = isAxiosResponse(resOrJson) ? resOrJson.data : resOrJson;
  return body?.data ?? body ?? null;
}

/**
 * Extrai o paginator completo de uma resposta paginada.
 * Retorna { data: [], current_page, last_page, total, per_page, ... }.
 *
 * @example
 * const page = unwrapPage(await axios.get('/api/books'));
 * books.value = page.data;
 * pagination.current_page = page.current_page;
 */
export function unwrapPage(resOrJson) {
  const body = isAxiosResponse(resOrJson) ? resOrJson.data : resOrJson;
  const paginator = body?.current_page !== undefined ? body : body?.data;
  return paginator ?? { data: [], current_page: 1, last_page: 1, total: 0, per_page: 10 };
}

function isAxiosResponse(x) {
  return x && typeof x === 'object' && 'data' in x && 'status' in x;
}
