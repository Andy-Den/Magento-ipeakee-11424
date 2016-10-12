var child_process = require('child_process');

describe("PHP Lint", function() {

    var tasksDir = "./test.d/task";

    it("Run PHP Lint", function (done) {

        var exit_code = 0;

        child_process.exec('./phplint.sh', {cwd: tasksDir, timeout:60000, env: process.env}, function (error, stdout, stderr) {

            if (error) {
                exit_code = error.code;
            }
            expect(exit_code).toEqual(0, stderr);
            done();
        });
    }, 60000);
});