import { useMixpanel } from '@astnext/context/mixpanel/hooks/useMixpanel';
import { type Callback, type Dict, type RequestOptions } from 'mixpanel-browser';
import { useCallback } from 'react';

export const useMixpanelTrack = () => {
  const { initialized, mixpanel } = useMixpanel();

  return useCallback(
    (
      event_name: string,
      properties?: Dict,
      optionsOrCallback?: RequestOptions | Callback,
      callback?: Callback
    ) => {
      if (initialized) {
        mixpanel.track(event_name, properties, optionsOrCallback, callback);
      }
    },
    [initialized, mixpanel]
  );
};
