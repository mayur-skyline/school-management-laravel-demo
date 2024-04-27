import { useMemo } from 'react';

const PROD_DOMAIN = 'https://steer.global';
const MIRROR_DOMAIN = 'https://mirror.steer.global';

export function isProduction() {
  return Boolean([PROD_DOMAIN, MIRROR_DOMAIN].includes(window.origin));
}

export function useIsProduction() {
  const status = useMemo(() => isProduction(), []);

  return status;
}
