  /*
   All Emoncms code is released under the GNU Affero General Public License.
   See COPYRIGHT.txt and LICENSE.txt.

    ---------------------------------------------------------------------
    Emoncms - open source energy visualisation
    Part of the OpenEnergyMonitor project:
    http://openenergymonitor.org
  */

        //--------------------------------------------------------------
        // Bar graph
        //--------------------------------------------------------------
        function bargraph(data,barwidth,mode)
        {
          $.plot($("#placeholder"), [{color: "#0096ff", data: data}], 
          {
            bars: { show: true,align: "center",barWidth: (barwidth*1000),fill: true },
            grid: { show: true, hoverable: true, clickable: true },
            xaxis: { mode: "time", localTimezone: true, minTickSize: [1, mode], tickLength: 1}
          });
        }

        function instgraph(data)
        {
          $.plot($("#placeholder"), [{color: "#0096ff", data: data}], 
          {
            lines: { show: true, fill: true },
            grid: { show: true, hoverable: true },
            xaxis: { mode: "time" , localTimezone: true, min: start, max: end },
            selection: { mode: "x"}
          });
        }



     



