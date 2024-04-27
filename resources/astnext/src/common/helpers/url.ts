import urlJoin from 'url-join';

export function getAssetUrl(path: string) {
  return urlJoin(process.env.MIX_AST_ASSETS_URL || '', path);
}
