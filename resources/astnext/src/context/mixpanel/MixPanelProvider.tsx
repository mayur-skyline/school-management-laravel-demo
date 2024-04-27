import { useUserConfigs } from '@astnext/hooks/data/useUserConfigs';
import { useStore } from '@astnext/hooks/useStore';
import CryptoJS from 'crypto-js';
import mixpanelService from 'mixpanel-browser';
const environment = getEnvironment();
import { type Action } from '@astnext/@types';
import { getEnvironment } from '@astnext/common/helpers';
import React, { type Dispatch, type Reducer, createContext, useEffect, useReducer } from 'react';

import { useAppVisit } from '.';

export enum MIXPANEL_ACTIONS {
  setInitialized = 'SET_INITIALIZED',
  setMixpanel = 'SET_MIXPANEL',
}

export type MixpanelContext = {
  initialized: boolean;
  mixpanel;
};

const INITIAL_CONTEXT: MixpanelContext = {
  initialized: false,
  mixpanel: mixpanelService,
};

export const MixpanelContext = createContext<
  [MixpanelContext, Dispatch<Action<MIXPANEL_ACTIONS>>] | null
>(null);

MixpanelContext.displayName = 'MixpanelContext';

export const MixpanelReducer = (
  state: MixpanelContext,
  { type, payload }: Action<MIXPANEL_ACTIONS>
): MixpanelContext => {
  switch (type) {
    case MIXPANEL_ACTIONS.setInitialized: {
      return {
        ...state,
        initialized: payload,
      };
    }
    case MIXPANEL_ACTIONS.setMixpanel: {
      return {
        ...state,
        mixpanel: payload,
      };
    }
    default:
      throw new Error('Invalid store action ');
  }
};

type MixPanelProvider = React.HTMLAttributes<HTMLElement>;

export const MixPanelProvider: React.FC<MixPanelProvider> = (props) => {
  const [state, dispatch] = useReducer<Reducer<MixpanelContext, Action<MIXPANEL_ACTIONS>>>(
    MixpanelReducer,
    INITIAL_CONTEXT
  );

  const { initialized, mixpanel } = state;

  const [
    {
      session: { user, school_id },
    },
  ] = useStore();

  const {
    tracking_consent: { accepted, answered },
  } = useUserConfigs();

  // useAppVisit();

  useEffect(() => {
    if (!initialized && accepted && answered) {
      // initialize mixpanel
      mixpanel.init(process.env.MIX_MIXPANEL_TOKEN, {
        debug: environment !== 'PROD',
        track_pageview: true,
        persistence: 'localStorage',
        ip: false,
      });

      const hashedUserId = CryptoJS.SHA256(`${school_id}_${user.id}`).toString();
      const hashedSchoolId = CryptoJS.SHA256(school_id).toString();

      mixpanel.identify(hashedUserId);

      mixpanel.people.set({
        $created: new Date(),
        $level: user.level,
        $gender: user.gender,
        $school_id: hashedSchoolId,
      });

      dispatch({ type: MIXPANEL_ACTIONS.setInitialized, payload: true });
    }
  }, [accepted, answered, initialized, mixpanel, school_id, user.gender, user.id, user.level]);

  return <MixpanelContext.Provider value={[state, dispatch]} {...props} />;
};
