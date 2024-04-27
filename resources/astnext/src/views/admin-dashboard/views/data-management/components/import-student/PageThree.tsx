import { TableBody, TableCell } from '@mui/material';
import Table from '@mui/material/Table';
import TableHead from '@mui/material/TableHead';
import TableRow from '@mui/material/TableRow';
import React, { FC, useState } from 'react';

import { StudentMisData } from '../../../../../../common/models/WondeMisid';
type PageThreeType = {
  misData: StudentMisData;
  toNext: () => void;
};
const PageThree: FC<PageThreeType> = ({ misData, toNext }) => {
  const [understand, setUnderstand] = useState(false);
  return (
    <div className="a3-part absolute px-12 py-2.5">
      <p>Below is a sample of data already in our system</p>
      <Table className="mis-student-data-table">
        <TableHead className="Mis-data-table-header">
          <TableRow>
            {['MIS ID', 'Firstname', 'Username', 'Year', 'DoB', 'Gender'].map((ele, i) => (
              <TableCell
                className="font-bold text-center text-xl border-b-2 border-t-2 border-r-2 border-gray-400"
                key={i}
                style={{ borderColor: 'rgba(224, 224, 224, 1)', fontWeight: 700 }}
              >
                {ele}
              </TableCell>
            ))}
          </TableRow>
        </TableHead>
        <TableBody>
          {misData.sample_tbl_message.trim().length === 0 ? (
            misData.sample_tbl_data.map((row, key) => {
              if (key > 4) {
                return;
              }
              return (
                <TableRow key={key}>
                  {[row.mis_id, row.firstname, row.username, row.year, row.dob, row.gender].map(
                    (ele, i) => (
                      <TableCell
                        className="font-bold text-center text-xl border-b-2 border-t-2 border-r-2 border-gray-400"
                        key={i}
                        style={{ borderColor: 'rgba(224, 224, 224, 1)' }}
                      >
                        {ele}
                      </TableCell>
                    )
                  )}
                </TableRow>
              );
            })
          ) : (
            <TableRow>
              <TableCell
                colSpan={6}
                className="font-bold text-center text-xl border-2  border-gray-400"
                style={{ borderColor: 'rgba(224, 224, 224, 1)' }}
              >
                {misData.sample_tbl_message}
              </TableCell>
            </TableRow>
          )}
        </TableBody>
      </Table>
      {misData.data_status !== 'nomatchdata' ? (
        <div className="form-check mt-5">
          <input
            className="form-check-input mr-2 mb-8"
            type="checkbox"
            id="check1"
            name="option1"
            value="I understand"
            checked={understand}
            onChange={() => setUnderstand(!understand)}
          />
          <label className="form-check-label">
            We accept that it is our responsibility to match newly imported student data with
            previous student data records.*
          </label>
          <br></br>
          <em>
            *Additional fees may apply if STEER is required to match incorrectly formatted student
            data at a later date.
          </em>
        </div>
      ) : (
        <div className="form-check mt-5">
          If you need assistance, contact STEER Technical Support.
        </div>
      )}
      {understand && (
        <div className="mt-3">
          <p className="mt-3 mb-2 text-xl text-red-600 font-semibold">
            To continue with the data import click on Next
          </p>
          <button
            onClick={toNext}
            className="bg-yellow-500 hover:bg-yellow-700 text-black font-bold py-2 px-4 rounded"
          >
            Next
          </button>
        </div>
      )}
    </div>
  );
};
export default PageThree;
