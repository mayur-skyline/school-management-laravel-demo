import { Radio, Table, TableBody, TableHead, TableRow } from '@mui/material';
import React, { FC } from 'react';

import { checkOddOrEven } from '../../../../../common/helpers';
import HtmlTooltip from '../../../../../components/base/tooltip/HtmlTooltip';
import TrackerCheckbox from '../../training-tracker/components/TrackerCheckbox';
import { permState } from '../PermissionReducer';
import { TableCell } from '../Permissions';

const HighLevelTable: FC<{
  headerData: {
    campus_array: string[];
  };
  headerName: string[];
  permissons: permState[];
  onAllClick: (id: number) => void;
  onRadioClick: (rId: number, cId: number) => void;
  onCheckboxClick: (rId: number, cId: number) => void;
}> = ({ headerData, headerName, permissons, onAllClick, onRadioClick, onCheckboxClick }) => {
  return (
    <Table className="training-tracker-table" stickyHeader>
      <TableHead className="cohort-data-table-header">
        <TableRow>
          <TableCell
            component="th"
            style={{
              width: '250px',
            }}
          >
            <h3 className="text-[17px] font-bold inline"> All Teachers </h3>
          </TableCell>
          {headerName.map((item, num) => {
            return (
              <TableCell key={num} component="th" align="center">
                <div className="text-gray-500">
                  <p title="Safeguarding" className="truncate mb-2">
                    {item}
                  </p>
                  <div className="flex justify-evenly flex-row">
                    {num === 0 ? (
                      <p
                        role="button"
                        className="text-[10px] text-gray-400"
                        onClick={onAllClick.bind(null, num)}
                      >
                        Tick All
                      </p>
                    ) : null}
                    {num === 1
                      ? headerData.campus_array.map((ele, i) => (
                          <HtmlTooltip title={ele} key={i}>
                            <div key={ele} className="mx-4">
                              {ele.substring(0, 3)}
                            </div>
                          </HtmlTooltip>
                        ))
                      : null}
                  </div>
                </div>
              </TableCell>
            );
          })}
        </TableRow>
      </TableHead>

      <TableBody>
        {permissons[0]?.row?.staff_id ? (
          permissons.map((tracker, key) => (
            <TableRow
              key={key}
              style={{
                background: checkOddOrEven(key) === 'ODD' ? '#FAFAFD' : 'white',
              }}
            >
              <TableCell
                style={{
                  position: 'sticky',
                  left: 225,
                  zIndex: 1,
                  background: checkOddOrEven(key) === 'ODD' ? '#FAFAFD' : 'white',
                }}
              >
                <h3 className="text-base font-bold">{tracker.row.staff_name}</h3>
              </TableCell>
              <TableCell align="center">
                <Radio
                  checked={tracker.allCampus || false}
                  onChange={onRadioClick.bind(null, key)}
                  name={'permission-tracker'}
                  sx={{
                    color: '#e5e7eb',
                    '&.Mui-checked': {
                      color: '#ffd44b',
                    },
                  }}
                  size={'small'}
                />
              </TableCell>
              <TableCell align="center">
                <div className="flex justify-evenly flex-row">
                  {tracker.campus.map((ele, ind) => {
                    return (
                      <div key={ind} className="mx-4">
                        <HtmlTooltip title={headerData.campus_array[ind]}>
                          <span>
                            <TrackerCheckbox
                              checked={ele}
                              onChange={onCheckboxClick.bind(null, key, ind)}
                            />
                          </span>
                        </HtmlTooltip>
                      </div>
                    );
                  })}
                </div>
              </TableCell>
            </TableRow>
          ))
        ) : (
          <TableRow>
            <TableCell colSpan={9}>
              <p className="text-center text-gray-500 my-20">Data not available</p>
            </TableCell>
          </TableRow>
        )}
      </TableBody>
    </Table>
  );
};
export default HighLevelTable;
