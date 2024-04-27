import { useCallback } from 'react';

import { STORE_ACTIONS } from '../context/store';
import { useUserConfigs } from './data/useUserConfigs';
import { useStore } from './useStore';

export const useHasSeenNewTermFlow = () => {
  const { updateConfigs, has_seen_new_term_flow } = useUserConfigs();

  const [{ hasSeenNewTermFlow }, dispatch] = useStore();

  const setHasSeenNewTermFlow = useCallback(
    (value: boolean) => {
      dispatch({ type: STORE_ACTIONS.setHasSeenNewTermFlow, payload: value });
      updateConfigs({
        has_seen_new_term_flow: value,
      });
    },
    [dispatch, updateConfigs]
  );

  return {
    hasSeenNewTermFlow: has_seen_new_term_flow || hasSeenNewTermFlow,
    setHasSeenNewTermFlow,
  };
};
