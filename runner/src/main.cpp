#include <iostream>
#include <pqxx/pqxx>
#include <queue>

void queue_submitted_solutions(pqxx::work &tx, std::queue<uint64_t> &queue)
{
    auto submitted_solutions = tx.query<uint64_t>("UPDATE solutions SET status='queued' WHERE status='submitted' RETURNING id;");
    for (auto [id] : submitted_solutions)
    {
        queue.push(id);
        std::cout << "Queued #" << id << std::endl;
    }
}

int main()
{
    std::queue<uint64_t> queued_solutions;
    std::string db_connection_string = std::getenv("RUNNER_PGSQL_CONNECTION");
    pqxx::connection c{db_connection_string};
    std::cout << "Connected to " << c.dbname() << std::endl;

    c.listen("solution_submitted", [&c, &queued_solutions](pqxx::notification notification)
             {
        pqxx::work tx{c};
        queue_submitted_solutions(tx, queued_solutions);
        tx.commit(); });
    pqxx::work tx{c};
    queue_submitted_solutions(tx, queued_solutions);                                                     // queue solutions submitted before listen
    auto solutions_marked_queued = tx.query<uint64_t>("SELECT id FROM solutions WHERE status='queued';"); // requeue solutions already marked queued
    for (auto [id] : solutions_marked_queued)
    {
        queued_solutions.push(id);
        std::cout << "Add solution #" << id << " to queue" << std::endl;
    }
    tx.commit();
    std::cout << "Starting main loop" << std::endl;
    while (true)
    {
        if (queued_solutions.empty())
            c.await_notification();
        if (queued_solutions.empty())
            continue; // no solutions were queued
        uint64_t id = queued_solutions.front();
        std::cout << "Processing #" << id << std::endl;
        pqxx::result result;
        queued_solutions.pop();
        {
            pqxx::work tx{c};
            result = tx.exec("SELECT code FROM solutions WHERE id=$1", id);
            if (result.size() != 1)
            {
                std::cerr << "Cannot find solution #" << id << " from local queue in DB" << std::endl;
                continue;
            }
            tx.exec("UPDATE solutions SET status='testing' WHERE id=$1", id);
            tx.commit();
        }
        std::cout << "Testing #" << id << std::endl;


        {
            pqxx::work tx{c};
            tx.exec("UPDATE solutions SET status='done' WHERE id=$1", id);
            tx.commit();
        }
        std::cout << "#" << id << " done" << std::endl;
    }
    return 0;
}