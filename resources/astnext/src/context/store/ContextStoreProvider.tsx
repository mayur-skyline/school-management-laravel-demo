import AppError from '@astnext/common/errors/AppError';
import { type TrackingConsent } from '@astnext/common/models/UserConfigs';
import { useSessionStorage } from '@astnext/hooks/useSessionStorage';
import { isEmpty } from 'lodash';
import * as process from 'process';
import React, {
  type Dispatch,
  type Reducer,
  createContext,
  useEffect,
  useReducer,
  useState,
} from 'react';
import { useCookies } from 'react-cookie';
import Lottie from 'react-lottie';
import { useLocation, useNavigate } from 'react-router-dom';

import { type Action } from '../../@types';
import sessionLoader from '../../assets/lotte-animations/session-loader.json';
import Session, { type SessionType } from '../../common/models/Session';
import { useShowError } from '../modals/ModalProvider';
import { type Store, DEFAULT_STORE, STORE_ACTIONS, StoreReducer } from './index';

export type StoreContextType = [Store, Dispatch<Action<STORE_ACTIONS>>];
export const StoreContext = createContext<StoreContextType | null>(null);
StoreContext.displayName = 'StoreContext';

type StoreReducer = Reducer<Store, Action<STORE_ACTIONS>>;

const ContextStoreProvider: React.FC<React.HTMLAttributes<HTMLElement>> = ({
  children,
  ...props
}) => {
  const [state, dispatch] = useReducer<StoreReducer>(StoreReducer, DEFAULT_STORE);
  const [{ user: sessionStorageUser = {} }] = useSessionStorage<SessionType>('session', {});

  const [loading, setLoading] = useState(false);
  const [cookies] = useCookies(['jwt_token']);
  const navigate = useNavigate();

  const { session, isStoreLoaded } = state;
  const location = useLocation();
  const { showError } = useShowError();

  const isSessionSet = Boolean(session && session.user);

  useEffect(() => {
    if (!cookies.jwt_token) {
      if (isEmpty(sessionStorageUser) || sessionStorageUser?.level == 1) {
        window.location.href = `${process.env.MIX_ASTNEXT_APP_URL}/astnext-logout`;
        return;
      }

      if (!location.pathname.match('half-login')) {
        // Get the URLSearchParams object from location.search
        const searchParams = new URLSearchParams(location.search);
        // Remove the "redirect_url" parameter
        searchParams.delete('redirect_url');
        // Get the updated search string
        const updatedSearchString = searchParams.toString();

        navigate(
          `/half-login${
            location.pathname.match('half-login')
              ? ''
              : `?callback_url=${encodeURIComponent(
                  process.env.MIX_ASTNEXT_APP_URL +
                    '/ast-next' +
                    location.pathname +
                    (updatedSearchString ? `?${updatedSearchString}` : '')
                )}`
          }`
        );
      }

      return;
    }

    setLoading(true);

    new Promise((resolve, reject) => {
      setTimeout(() => {
        const school_id = sessionStorage.getItem('school_id');
        const school_code = sessionStorage.getItem('school_code');

        if (!school_id || !school_code) {
          window.location.reload();
          reject(new AppError('School ID and School code not set on session'));
        } else {
          resolve({ school_id, school_code });
        }
      }, 1000);
    })
      .then(({ school_id, school_code }) => {
        return Session.getUserSession().then((sessionData) => {
          const newSession = new Session(sessionData);
          const { user } = newSession;
          const tracking_consent =
            newSession?.user_configs?.tracking_consent || ({} as TrackingConsent);

          const { local_filters, has_seen_new_term_flow } = newSession.user_configs;

          dispatch({ type: STORE_ACTIONS.setCurrentFilters, payload: local_filters });
          dispatch({ type: STORE_ACTIONS.setHasSeenNewTermFlow, payload: has_seen_new_term_flow });

          dispatch({ type: STORE_ACTIONS.setSession, payload: newSession });
          dispatch({ type: STORE_ACTIONS.setIsStoreLoaded, payload: true });

          if (school_code === '999037' || school_id === '37') {
            if (
              location.pathname.match('/group-dashboard') ||
              location.pathname.match('/training')
            ) {
              return;
            } else {
              navigate('/group-dashboard');
            }
          } else if (user.level !== 1) {
            if ([3, 5, 6].includes(user.level) && newSession?.school_details?.onboarding) {
              navigate('/new-year-setup');
            } else if (!newSession.hasCompletedOnboarding()) {
              navigate('/staff-onboarding');
            } else if (!tracking_consent?.answered) {
              navigate('/tracking-consent');
            }
          }
        });
      })
      .catch((error) => {
        showError(error);
        throw new Error(`[ERROR]: Failed to get user session: ${error.message}`);
      })
      .finally(() => {
        setTimeout(() => {
          setLoading(false);
        }, 1000);
      });
  }, []);

  if (location.pathname.match('half-login')) {
    return (
      <StoreContext.Provider value={[state, dispatch]} {...props}>
        {children}
      </StoreContext.Provider>
    );
  }

  if (loading) {
    return (
      <div
        className="fixed inset-0 flex items-center justify-center text-center"
        style={{ backgroundColor: 'rgba(255, 255, 255, 0.8)' }}
      >
        <div className="h-[250px] mb-12">
          <Lottie
            options={{
              loop: false,
              autoplay: true,
              animationData: sessionLoader,
              rendererSettings: {
                preserveAspectRatio: 'xMidYMid slice',
              },
            }}
            height={250}
            width={250}
          />
        </div>
      </div>
    );
  }

  if (isSessionSet) {
    return (
      <StoreContext.Provider value={[state, dispatch]} {...props}>
        {isStoreLoaded && children}
      </StoreContext.Provider>
    );
  }
};

export default ContextStoreProvider;
