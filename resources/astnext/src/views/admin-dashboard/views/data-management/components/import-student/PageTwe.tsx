import { useStore } from '@astnext/hooks/useStore';
import React, { type FC, useEffect, useState } from 'react';

import { type SchoolList } from '../../../../../../common/models/SchoolInfo';
import CircularProgress from '../../../../../../components/overrides/circular-progress/CircularProgress';
import { useWondePop } from '../../../../../../hooks/data/useWondePop';
type PageTweType = {
  listWonde: SchoolList;
  toNext: (pageNum: string[]) => void;
};
const PageTwe: FC<PageTweType> = ({ listWonde, toNext }) => {
  const [data, setData] = useState<{
    page: number;
    checkpoprow: number;
    multiwondeid: number;
    misIdList: string[];
  }>({ page: 0, checkpoprow: 0, multiwondeid: 0, misIdList: [] });
  const [resend, setResend] = useState(1);
  const [
    {
      session: { school_id },
    },
  ] = useStore();

  const { loading, resource } = useWondePop(
    {
      page: data.page,
      checkpoprow: data.checkpoprow,
      multiwondeid: data.multiwondeid,
    },
    resend
  );

  useEffect(() => {
    if (resource.data) {
      const misIdlist = [];
      for (const id in resource.data.mis_id_arr) {
        misIdlist.push(resource.data.mis_id_arr[`${id}`]);
      }

      if (resource.data.status === 'success') {
        if (resource.data.matched_poprow < 5) {
          setData((prev) => {
            return {
              ...prev,
              checkpoprow: resource.data.matched_poprow,
              page: resource.data.page_value,
              misIdList: prev.misIdList.concat(misIdlist),
            };
          });
          setResend(resend + 1);
        } else if (resource.data.matched_poprow >= 5) {
          setData((prev) => {
            return {
              ...prev,
              checkpoprow: resource.data.matched_poprow,
              page: resource.data.page_value,
              misIdList: prev.misIdList.concat(misIdlist),
            };
          });
          setResend(0);
        }
      }
      if (resource.data.status === 'nomoredata') {
        if (resource.data.matched_poprow >= 1) {
          setResend(0);
          setData((prev) => {
            return {
              ...prev,
              checkpoprow: resource.data.matched_poprow,
              page: resource.data.page_value,
              misIdList: prev.misIdList.concat(misIdlist),
            };
          });
        } else {
          if (resource.data.multi_wonde_id_status === 'No') {
            setData((prev) => {
              return {
                ...prev,
                checkpoprow: resource.data.matched_poprow,
                page: resource.data.page_value,
                misIdList: prev.misIdList.concat(misIdlist),
              };
            });
            setResend(0);
          } else if (resource.data.multi_wonde_id_status === 'Yes') {
            setData((prev) => {
              return {
                ...prev,
                checkpoprow: resource.data.matched_poprow,
                page: resource.data.page_value,
                multiwondeid: 1,
              };
            });
            setResend(resend + 1);
          }
        }
      }
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [resource]);
  const isLoading = loading || resend > 0;
  return (
    <div className="absolute px-12 pb-2.5">
      <div>
        <div className="">
          {(loading || resend > 0) && (
            <p className="mt-3 mb-4 text-xl text-red-600 font-semibold">
              Please wait while we compare the data with our system
            </p>
          )}
          <h2 className="mt-5 mb-2 text-xl">Information</h2>
          {listWonde && (
            <ul style={{ color: 'rgb(65, 64, 66)' }}>
              <li>{`Username   : ${listWonde.user_name}`}</li>
              <li>{`Passwords  : ${listWonde.password}`}</li>
              {listWonde?.campus &&
                !['74', '6', '185'].includes(school_id as string) &&
                listWonde.campus.map((ele, i) => {
                  const campuses = Object.keys(ele);
                  const listStr = ele[campuses[0]];
                  return (
                    <li key={i}>{`${campuses} : ${
                      listStr.length > 0 ? listStr : `No data for ${campuses}`
                    }`}</li>
                  );
                })}
              <li>{`House Type : ${listWonde.house}`}</li>
            </ul>
          )}
        </div>
      </div>
      <div>
        <div className="mt-3">
          {isLoading ? (
            <div className="pr-10 pl-8 inline-flex items-center justify-center">
              <CircularProgress />
              <h2 className="mx-4 pb-2 text-xl"> Processing...</h2>
            </div>
          ) : (
            <button
              onClick={toNext.bind(null, data.misIdList)}
              className="bg-yellow-500 hover:bg-yellow-700 text-black font-bold py-2 px-4 rounded"
            >
              Next
            </button>
          )}
        </div>
      </div>
    </div>
  );
};

export default PageTwe;
