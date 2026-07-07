import { useEffect, useState } from "react";

import { Login } from "./Components/Login";
import { useApi } from "./Components/useApi";

function App() {
  const { get, loading, error } = useApi();
  const [birds, setBirds] = useState([]);
  console.log(birds);
  useEffect( () => {
    get('/birds').then( (newBirds) => setBirds(newBirds) );
  }, [get]);
  return (
    <>
      <h1 className="bg-amber-500 text-center p-4">API Tester</h1>
      <div className="w-3xl mx-auto my-10">
        <Login />
      </div>
      {loading && <p>Loading</p>}
    </>
  );
}

export default App;
// https://base-ui.com/react/overview/quick-start
