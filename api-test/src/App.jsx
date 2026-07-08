import { useEffect, useState } from "react";

import { Login } from "./Components/Login";
import { useApi } from "./Components/useApi";
import { AddBird } from "./Components/AddBird";

function App() {
  const { get, loading, error } = useApi();
  const [birds, setBirds] = useState([]);
  const [token, setToken] = useState(null);
  console.log(birds);
  useEffect(() => {
    get("/birds").then((newBirds) => setBirds(newBirds));
  }, [get]);

  const handleLogin = (result) => {
    const newToken = result.data.token;
    setToken(newToken);
  };

  return (
    <>
      <h1 className="bg-amber-500 text-center p-4">API Tester</h1>
      <div className="w-3xl mx-auto my-10">
        {!token && <Login onLogin={handleLogin} />}
        {token && (
          <>
            <code className="text-xs bg-amber-700 p-2 text-white rounded-sm my-4 inline-block">{token}</code>
            {birds?.data?.length > 0 && <AddBird token={token} />}
          </>
        )}

        {loading && <p>Loading</p>}
        {birds?.data?.length > 0 && (
          <ul className="columns-3 my-10">
            {birds.data.map((bird) => (
              <li key={bird.id}>{bird.common_name}</li>
            ))}
          </ul>
        )}
      </div>
    </>
  );
}

export default App;
// https://base-ui.com/react/overview/quick-start
