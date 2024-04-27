import { captureException } from '@sentry/react';
import React, {
  type Dispatch,
  type Reducer,
  createContext,
  useCallback,
  useContext,
  useEffect,
  useReducer,
} from 'react';
import { toast } from 'react-toastify';

import { type Action } from '../../@types';
import { TOAST_OPTIONS } from '../../common/configs';
import { type RequestError } from '../../common/errors/RequestError';
import ErrorModal from '../../components/overrides/modal/ErrorModal';
import WarningToast from '../../components/toasts/WarningToast/WarningToast';
import {
  type ModalStore,
  DEFAULT_MODAL_STORE,
  MODAL_STORE_ACTIONS,
  ModalStoreReducer,
} from './index';

export type ModalStoreContextType = [ModalStore, Dispatch<Action<MODAL_STORE_ACTIONS>>];
export const ModalStoreContext = createContext<ModalStoreContextType | null>(null);

ModalStoreContext.displayName = 'ModalStoreContext';

type ModalStoreReducer = Reducer<ModalStore, Action<MODAL_STORE_ACTIONS>>;

const ModalStoreProvider: React.FC<React.HTMLAttributes<HTMLElement>> = ({
  children,
  ...props
}) => {
  const [state, dispatch] = useReducer<ModalStoreReducer>(ModalStoreReducer, DEFAULT_MODAL_STORE);
  return (
    <ModalStoreContext.Provider value={[state, dispatch]} {...props}>
      <ErrorModal />
      {children}
    </ModalStoreContext.Provider>
  );
};

export function useModal() {
  const context = useContext(ModalStoreContext);
  if (!context) {
    throw new Error('useModal must be used within a ModalStoreProvider');
  }
  return context;
}

export function useShowError() {
  const [, dispatch] = useModal();

  const showError = useCallback(
    (error: RequestError) => {
      if (error?.message && (!error?.isCancelled || !error.isCancelled())) {
        if (error.isExpected) {
          toast(<WarningToast>{error.message}</WarningToast>, TOAST_OPTIONS);
        } else if (!error.statusCode || error.statusCode === 500) {
          dispatch({ type: MODAL_STORE_ACTIONS.toggleErrorModal, payload: { open: true, error } });
          captureException(error);
        } else if (error.statusCode === 429) {
          dispatch({ type: MODAL_STORE_ACTIONS.toggleTooMuchTrafficErrorModal, payload: true });
          captureException(error);
        } else if (error.statusCode === 401) {
          // console.log(error);
        } else {
          toast(<WarningToast>{error.message}</WarningToast>, TOAST_OPTIONS);
        }
      }
    },
    [dispatch]
  );

  return { showError };
}

export function useErrorModal(error: RequestError) {
  const [, dispatch] = useModal();

  useEffect(() => {
    if (error?.message && (!error?.isCancelled || !error.isCancelled())) {
      if (error.isExpected) {
        toast(<WarningToast>{error.message}</WarningToast>, TOAST_OPTIONS);
      } else if (!error.statusCode || error.statusCode === 500) {
        dispatch({ type: MODAL_STORE_ACTIONS.toggleErrorModal, payload: { open: true, error } });
        captureException(error);
      } else if (error.statusCode === 429) {
        dispatch({ type: MODAL_STORE_ACTIONS.toggleTooMuchTrafficErrorModal, payload: true });
        captureException(error);
      } else if (error.statusCode === 401) {
        // console.log(error);
      } else {
        toast(<WarningToast>{error.message}</WarningToast>, TOAST_OPTIONS);
      }
    }
  }, [dispatch, error]);
}

export default ModalStoreProvider;
