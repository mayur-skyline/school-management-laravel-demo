import type { ISchool } from '@astnext/common/models/School';
import { useSessionStorage } from '@astnext/hooks/useSessionStorage';
import Auth from '@atom/common/models/Auth';
import { useEffect, useState } from 'react';

export const useSchoolAuthCheck = (onSuccess: () => void = null, onFailure: () => void) => {
  const [loading, setLoading] = useState(true);
  const [school, setSchool] = useState<ISchool>(null);
  const [steer_cfbfab, setSteerCfbfab] = useSessionStorage('steer_cfbfab', null);
  const [isMounted, setIsMounted] = useState(true);

  useEffect(() => {
    if (isMounted) {
      setLoading(true);
      Auth.checkStep1()
        .then((data) => {
          setSteerCfbfab(data.steer_cfbfab);
        })
        .catch(() => {
          onFailure();
        });

      const school_id = sessionStorage.getItem('school_id');
      const school_code = sessionStorage.getItem('school_code');
      const school_name = sessionStorage.getItem('school_name');

      if (school_id && school_code && school_name) {
        setSchool({
          school_id,
          school_code,
          school_name,
        });
        if (typeof onSuccess === 'function') {
          onSuccess();
        }
      } else {
        if (typeof onFailure === 'function') {
          onFailure();
        }
      }
      setLoading(false);
      // eslint-disable-next-line react-hooks/exhaustive-deps
    }

    return () => {
      setIsMounted(false);
    };
  }, [isMounted]);

  return { loading, school, steer_cfbfab, setSchool };
};
