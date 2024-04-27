import { useMixpanelTrack } from '@astnext/context/mixpanel/hooks/useMixpanelTrack';
import { useUserConfigs } from '@astnext/hooks/data/useUserConfigs';
import { useEffect } from 'react';

export const useModuleVisit = (
  module: string,
  payloads: Record<string, any> = {},
  delaySeconds = 0
) => {
  const {
    tracking_consent: { accepted, answered },
  } = useUserConfigs();

  const track = useMixpanelTrack();

  useEffect(() => {
    if (answered && accepted) {
      const timeout = window.setTimeout(() => {
        track('Module Visit', {
          ...payloads,
          module,
        });
      }, delaySeconds * 1000);

      return () => window.clearTimeout(timeout);
    }
  }, [module, delaySeconds, answered, accepted, payloads, track]);
};
