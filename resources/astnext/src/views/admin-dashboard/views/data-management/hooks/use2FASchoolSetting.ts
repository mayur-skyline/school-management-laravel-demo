import DataManagement from '@astnext/common/models/DataManagement';
import { useErrorModal, useShowError } from '@astnext/context/modals/ModalProvider';
import { useAsync } from '@astnext/hooks/useAsync';
import { useEffect, useState } from 'react';

export const use2FASchoolSetting = () => {
  const [status, setStatus] = useState<number>(0);
  const { showError } = useShowError();

  const { loading, execute, error } = useAsync({
    asyncFunc: () => DataManagement.getTwoFactorAuth().then(setStatus),
    immediate: false,
  });

  function update2faStatus(newStatus: number) {
    const oldStatus = status;
    setStatus(newStatus);
    DataManagement.setTwoFactorAuth(newStatus).catch((error) => {
      showError(error);
      setStatus(oldStatus);
    });
  }

  useErrorModal(error);

  useEffect(() => {
    execute();
  }, []);

  return { loading, status, update2faStatus };
};
